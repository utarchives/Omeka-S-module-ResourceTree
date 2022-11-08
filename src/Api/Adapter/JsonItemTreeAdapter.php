<?php
namespace ResourceTree\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Api\Response;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use ResourceTree\Api\Representation\JsonItemTreeRepresentation;
use ResourceTree\Entity\JsonItemTree;


class JsonItemTreeAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return "json_item_trees";
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
    }

    public function getRepresentationClass()
    {
        return JsonItemTreeRepresentation::class;
    }

    public function getEntityClass()
    {
        return JsonItemTree::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
    }
    /**
     *
     * {@inheritDoc}
     * @see \Omeka\Api\Adapter\AbstractEntityAdapter::create()
     * add new related item
     */
    public function create(Request $request)
    {
        $entity = new JsonItemTree();
        $this->authorize($entity, Request::CREATE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $content = $request->getContent();
        $connection->insert('json_item_tree', $content);
        return new Response($entity);
    }

    public function delete(Request $request) {
        // reset json_item_tree data
        $entity = new JsonItemTree();
        $this->authorize($entity, Request::BATCH_DELETE);
        $connection = $this->serviceLocator->get('Omeka\Connection');
        $sql = <<<'SQL'
truncate table json_item_tree;
SQL;
        $connection->exec($sql);
        return new Response($entity);
    }

}