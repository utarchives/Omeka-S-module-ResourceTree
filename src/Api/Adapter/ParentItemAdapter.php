<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Api\Response;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Resource;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\ParentItemRepresentation;
use ResourceTree\Entity\ParentItem;


class ParentItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "parent_items";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return ParentItemRepresentation::class;
    }

    public function getEntityClass()
    {
        return ParentItem::class;
    }
    /**
     * {@inheritDoc}
     */
    public function buildQuery(QueryBuilder $qb, array $query)
    {
        // $entity = $this->getEntityClass();
        $entity = 'omeka_root';
        if (isset($query['root'])) {
            $qb->andWhere("$entity.depth = 1");
        }
        if (isset($query['child_item_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $entity . '.childItemId',
                $this->createNamedParameter($qb, $query['child_item_id'])));
        }
    }

     /**
     * {@inheritDoc}
     */
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        // $entity = $this->getEntityClass();
        $entity = 'omeka_root';
        $qb->addOrderBy("$entity.sort", 'asc');
        $qb->groupBy("$entity.id,$entity.sort,$entity.isHere,$entity.depth,$entity.childItemId,$entity.resourceClass,$entity.targetResourceClassId,$entity.title,$entity.resourceTemplate");
    }

    /**
     *
     * {@inheritDoc}
     * @see \Omeka\Api\Adapter\AbstractEntityAdapter::delete()
     * Delete all related items
     */
    public function delete(Request $request)
    {
        $entity = new ParentItem();
        $this->authorize($entity, Request::BATCH_DELETE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $sql = <<<'SQL'
delete from value where value_resource_id is not null;
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
        $entity = new ParentItem();
        $this->authorize($entity, Request::BATCH_CREATE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $content = $request->getContent();
        $connection->insert('value', $content);
        return new Response($entity);
    }

}