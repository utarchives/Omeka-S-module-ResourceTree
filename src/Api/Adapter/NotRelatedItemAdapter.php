<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use ResourceTree\Api\Representation\NotRelatedItemRepresentation;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Entity\NotRelatedItem;


class NotRelatedItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "not_related_items";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return NotRelatedItemRepresentation::class;
    }

    public function getEntityClass()
    {
        return NotRelatedItem::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildQuery(QueryBuilder $qb, array $query)
    {
        $entity = 'omeka_root';
        $this->buildResourceQuery($qb, $query);
        if (isset($query['id'])) {
            $qb->andWhere($qb->expr()->eq("$entity.id", $query['id']));
        }

        if (isset($query['media'])) {
            $mediaAlias = $this->createAlias();
            $qb->innerJoin(
                "$entity.media",
                $mediaAlias
                );
        }

    }

    /**
     * {@inheritDoc}
     */
    public function buildResourceQuery(QueryBuilder $qb, array $query)
    {
        // configure collaborate with Resource Tree
        $entity = 'omeka_root';
        $this->buildPropertyQuery($qb, $query, $entity);
        if (isset($query['search'])) {
            $this->buildPropertyQuery($qb, ['property' => [[
                'property' => null,
                'type' => 'in',
                'text' => $query['search'],
            ]]], $entity);
        }
        if (isset($query['owner_id'])) {
            $userAlias = $this->createAlias();
            $qb->innerJoin(
                $entity . '.owner',
                $userAlias
                );
            $qb->andWhere($qb->expr()->eq(
                "$userAlias.id",
                $this->createNamedParameter($qb, $query['owner_id']))
                );
        }

        if (isset($query['resource_class_label'])) {
            $resourceClassAlias = $this->createAlias();
            $qb->innerJoin(
                $entity . '.resourceClass',
                $resourceClassAlias
                );
            $qb->andWhere($qb->expr()->eq(
                "$resourceClassAlias.label",
                $this->createNamedParameter($qb, $query['resource_class_label']))
                );
        }

        if (isset($query['resource_class_id']) && is_numeric($query['resource_class_id'])) {
            $resourceClassAlias = $this->createAlias();
            $qb->innerJoin(
                $entity . '.resourceClass',
                $resourceClassAlias
                );
            $qb->andWhere($qb->expr()->eq(
                "$resourceClassAlias.id",
                $this->createNamedParameter($qb, $query['resource_class_id']))
                );
        }
        if (isset($query['parent_item_id'])) {
            $qb->andWhere($qb->expr()->neq($entity . '.id', $query['parent_item_id']));
            $paretnItemAlias = $this->createAlias();
            $qb->innerJoin(
                $entity . '.parentItem',
                $paretnItemAlias
                );
            $qb->andWhere($qb->expr()->eq($paretnItemAlias . '.id', $query['parent_item_id']));
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        if (is_string($query['sort_by'])) {
            $entity = "omeka_root";
            if ('resource_class_id' == $query['sort_by']) {
                $resourceClassAlias = $this->createAlias();
                $qb->leftJoin("$entity.resourceClass", $resourceClassAlias)
                ->addOrderBy("$resourceClassAlias.id", $query['sort_order']);
            } else {
                parent::sortQuery($qb, $query);
            }
        }
    }

    /**
     * Build query on value.
     *
     * Query format:
     *
     *   - property[{index}][joiner]: "and" OR "or" joiner with previous query
     *   - property[{index}][property]: property ID
     *   - property[{index}][text]: search text
     *   - property[{index}][type]: search type
     *     - eq: is exactly
     *     - neq: is not exactly
     *     - in: contains
     *     - nin: does not contain
     *     - ex: has any value
     *     - nex: has no value
     *
     * @param QueryBuilder $qb
     * @param array $query
     * @param target entity
     */
    protected function buildPropertyQuery(QueryBuilder $qb, array $query, $targetEntity)
    {

        if (!isset($query['property']) || !is_array($query['property'])) {
            return;
        }
        $valuesJoin = $targetEntity . '.values';
        $where = '';
        foreach ($query['property'] as $queryRow) {
            if (!(is_array($queryRow)
                && array_key_exists('property', $queryRow)
                && array_key_exists('type', $queryRow)
                )) {
                    continue;
                }
                $propertyId = $queryRow['property'];
                $queryType = $queryRow['type'];
                $joiner = isset($queryRow['joiner']) ? $queryRow['joiner'] : null;
                $value = isset($queryRow['text']) ? $queryRow['text'] : null;

                if (!$value && $queryType !== 'nex' && $queryType !== 'ex') {
                    continue;
                }

                $valuesAlias = $this->createAlias();
                $positive = true;

                switch ($queryType) {
                    case 'neq':
                        $positive = false;
                    case 'eq':
                        $param = $this->createNamedParameter($qb, $value);
                        $predicateExpr = $qb->expr()->orX(
                            $qb->expr()->eq("$valuesAlias.value", $param),
                            $qb->expr()->eq("$valuesAlias.uri", $param)
                            );
                        break;
                    case 'nin':
                        $positive = false;
                    case 'in':
                        $param = $this->createNamedParameter($qb, "%$value%");
                        $predicateExpr = $qb->expr()->orX(
                            $qb->expr()->like("$valuesAlias.value", $param),
                            $qb->expr()->like("$valuesAlias.uri", $param)
                            );
                        break;
                    case 'nres':
                        $positive = false;
                    case 'res':
                        $predicateExpr = $qb->expr()->eq(
                        "$valuesAlias.valueResource",
                        $this->createNamedParameter($qb, $value)
                        );
                        break;
                    case 'nex':
                        $positive = false;
                    case 'ex':
                        $predicateExpr = $qb->expr()->isNotNull("$valuesAlias.id");
                    default:
                        continue 2;
                }

                $joinConditions = [];
                // Narrow to specific property, if one is selected
                if ($propertyId) {
                    $joinConditions[] = $qb->expr()->eq("$valuesAlias.property", (int) $propertyId);
                }

                if ($positive) {
                    $whereClause = '(' . $predicateExpr . ')';
                } else {
                    $joinConditions[] = $predicateExpr;
                    $whereClause = $qb->expr()->isNull("$valuesAlias.id");
                }

                if ($joinConditions) {
                    $qb->leftJoin($valuesJoin, $valuesAlias, 'WITH', $qb->expr()->andX(...$joinConditions));
                } else {
                    $qb->leftJoin($valuesJoin, $valuesAlias);
                }

                if ($where == '') {
                    $where = $whereClause;
                } elseif ($joiner == 'or') {
                    $where .= " OR $whereClause";
                } else {
                    $where .= " AND $whereClause";
                }
        }

        if ($where) {
            $qb->andWhere($where);
        }
    }

    /**
     * Get a property entity by JSON-LD term.
     *
     * @param string $term
     * @return EntityInterface
     */
    public function getPropertyByTerm($term)
    {
        if (!$this->isTerm($term)) {
            return null;
        }
        list($prefix, $localName) = explode(':', $term);
        $dql = 'SELECT p FROM Omeka\Entity\Property p
        JOIN p.vocabulary v WHERE p.localName = :localName
        AND v.prefix = :prefix';
        return $this->getEntityManager()
        ->createQuery($dql)
        ->setParameters([
            'localName' => $localName,
            'prefix' => $prefix,
        ])->getOneOrNullResult();
    }

}