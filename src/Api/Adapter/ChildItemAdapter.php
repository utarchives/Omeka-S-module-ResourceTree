<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Api\Response;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Resource;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\ChildItemRepresentation;
use ResourceTree\Entity\ChildItem;


class ChildItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "child_items";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return ChildItemRepresentation::class;
    }

    public function getEntityClass()
    {
        return ChildItem::class;
    }
    /**
     * {@inheritDoc}
     */
    public function buildQuery(QueryBuilder $qb, array $query)
    {
        // $entity = $this->getEntityClass();
        $entity = 'omeka_root';
        if (isset($query['parent_item_id'])) {
            $qb->andWhere($qb->expr()->eq(
                "$entity.parentItemId",
                $this->createNamedParameter($qb, $query['parent_item_id'])));
        }
        if (isset($query['is_here'])) {
            $qb->andWhere($qb->expr()->eq(
                "$entity.isHere",
                $this->createNamedParameter($qb, $query['is_here'])));
        }
    }

     /**
     * {@inheritDoc}
     */
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        parent::sortQuery($qb, $query);
        // $entity = $this->getEntityClass();
        $entity = 'omeka_root';
        $qb->groupBy("$entity.id,$entity.item,$entity.sort,$entity.isHere,$entity.depth,$entity.parentItemId,$entity.resourceClass,$entity.targetResourceClassId,$entity.title,$entity.resourceTemplate");
    }
}