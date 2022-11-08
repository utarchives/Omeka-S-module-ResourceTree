<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\LinkedItemRepresentation;
use ResourceTree\Entity\LinkedItem;


class LinkedItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "linked_items";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return LinkedItemRepresentation::class;
    }

    public function getEntityClass()
    {
        return LinkedItem::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        $entity = 'omeka_root';
        if (isset($query['parent_item_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $entity . '.parentItemId',
                $this->createNamedParameter($qb, $query['parent_item_id'])));
        }
    }
    public function sortQuery(QueryBuilder $qb, array $query)
    {

    }

}