<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Api\Response;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\ItemTreeRepresentation;
use ResourceTree\Entity\ItemTree;


class ItemTreeAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "item_trees";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return ItemTreeRepresentation::class;
    }

    public function getEntityClass()
    {
        return ItemTree::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['parent_item_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.parentItem',
                $this->createNamedParameter($qb, $query['parent_item_id']))
                );
        }
        if (isset($query['child_item_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.childItem',
                $this->createNamedParameter($qb, $query['child_item_id']))
                );
        }
    }
    /**
     * Get a property entity by JSON-LD term.
     *
     * @param string $term
     * @return EntityInterface
     */
    protected function getPropertyByTerm($term)
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
    /**
     * {@inheritDoc}
     */
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        $entityClass = $this->getEntityClass();
        if (is_string($query['sort_by'])) {
            $property = $this->getPropertyByTerm($query['sort_by']);
            if ($property) {
                $valuesAlias = $this->createAlias();
                $qb->leftJoin(
                    "$entityClass.values", $valuesAlias,
                    'WITH', $qb->expr()->eq("$valuesAlias.property", $property->getId())
                    );
                $qb->addOrderBy(
                    "GROUP_CONCAT($valuesAlias.value ORDER BY $valuesAlias.id)",
                    $query['sort_order']
                    );
            } else {
                parent::sortQuery($qb, $query);
            }
        }
    }

    /**
     *
     * {@inheritDoc}
     * @see \Omeka\Api\Adapter\AbstractEntityAdapter::delete()
     * Delete all related items
     */
    public function batchDelete(Request $request)
    {
        // reset item_tree data
        $entity = new ItemTree();
        $this->authorize($entity, Request::BATCH_DELETE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $sql = <<<'SQL'
truncate table item_tree;
SQL;
        $connection->exec($sql);
        return new Response($entity);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Omeka\Api\Adapter\AbstractEntityAdapter::create()
     * add new related item
     */
    public function create(Request $request)
    {
        $entity = new ItemTree();
        $this->authorize($entity, Request::CREATE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $content = $request->getContent();
        $connection->insert('item_tree', $content);
        return new Response($entity);
    }

    public function delete(Request $request) {
        // reset item_tree data
        $entity = new ItemTree();
        $this->authorize($entity, Request::BATCH_DELETE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $sql = <<<'SQL'
truncate table item_tree;
SQL;
        $connection->exec($sql);
        return new Response($entity);
    }

}