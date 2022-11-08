<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Api\Response;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\ItemTreeRepresentation;
use ResourceTree\Api\Representation\RootItemRepresentation;
use ResourceTree\Entity\ItemTree;
use ResourceTree\Entity\RootItem;


class RootItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "root_items";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return RootItemRepresentation::class;
    }

    public function getEntityClass()
    {
        return RootItem::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {

    }
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        
    }
}