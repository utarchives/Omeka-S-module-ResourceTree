<?php
namespace ResourceTree\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Omeka\Api\Manager as Api;
use Omeka\Settings\Settings;
use Omeka\Api\Request;

class RelatedItemsData extends AbstractPlugin
{
    /**
     * @var Api
     */
    protected $api;
    /**
     * @var Settings
     */
    protected $settings;
    protected $connection;
    public function __construct(
        Api $api,
        Settings $settings,
        $connection
        ) {
            $this->api = $api;
            $this->settings = $settings;
            $this->setTreeResourceClassIds();
            $this->connection = $connection;
    }
    /**
     * @var array
     */
    protected $resourceClasses;
    /**
     * @return array
     */
    public function getResourceClasses()
    {
        return $this->resourceClasses;
    }

    /**
     * setTreeResourceClassIds
     */
    protected function setTreeResourceClassIds()
    {
        $this->resourceClasses = array();
        $this->resourceClasses['folder'] = $this->getTreeResorceClasseId($this->settings->get('resource_tree_folder_class'));
        $this->resourceClasses['search_folder'] = $this->getTreeResorceClasseId($this->settings->get('resource_tree_search_folder_class'));
        $this->resourceClasses['document'] = $this->getTreeResorceClasseId($this->settings->get('resource_tree_document_class'));
    }

    public function getTargetDefaultDepth()
    {
        return $this->settings->get('resource_tree_default_depth');
    }

    public function getResourceValuePropertyId($id) {
        $stop = false;
        // updated
//         $response = $this->api->read('items', $id);
//         $item = $response->getContent();
        $sql = "select property_id
from
value
where
resource_id = ?
and
type in ('resource', 'resource:item')";
        $values = $this->connection->fetchAll($sql, [$id]);
        if (count($values) == 0) {
            return 0;
        }
        return $values[0]['property_id'];
//         foreach ($item->values() as $term => $property) {
//             foreach ($property['values'] as $value) {
//                 if (strcmp($value->type(), 'resource') == 0 || strcmp($value->type(), 'resource:item') == 0) {
//                     $stop = true;
//                     return $value->property()->id();
//                 }
//             }
//         }
//         return 0;
    }
    public function getFolderResourceClassId()
    {
        return $this->resourceClasses['folder'];
    }
    public function getSearchFolderResourceClassId()
    {
        return $this->resourceClasses['search_folder'];
    }
    public function getDocumentResourceClassId()
    {
        return $this->resourceClasses['document'];
    }

    public function getItemResourceClassId($id)
    {
        $query = ['id' => $id];
        $response = $this->api->search("items", $query);
        if ($response->getTotalResults() > 0) {
            return $response->getContent()[0]->resourceClass()->id();
        } else {
            return -1;
        }
    }
    /**
     * getTreeResourceClassId by setting value
     * @param string $targetItem
     * @return integer
     */
    public function getTreeResorceClasseId($targetItem)
    {
        $targetVocabulary = explode(':', $targetItem)[0];
        $targetClass = explode(':', $targetItem)[1];
        $query = ['prefix' => $targetVocabulary];
        $response = $this->api->read("vocabularies", $query);
        $vocabulary = $response->getContent()->id();
        $query = ['vocabulary' => $vocabulary, 'localName' => $targetClass];
        $response = $this->api->read("resource_classes", $query);
        if ($response) {
            return $response->getContent()->id();
        } else {
            return -1;
        }

    }
    /**
     * Get Parent should be
     * @param integer $child
     * @return array
     */
    public function getCorrectParent($child)
    {
        if ($child == $this->resourceClasses['folder']) {
            return array (['id' => $this->resourceClasses['folder'],
                'class' => $this->settings->get('resource_tree_folder_class')]
                , ['id' => $this->resourceClasses['search_folder'],
                    'class' => $this->settings->get('resource_tree_search_folder_class')]
            );
        }
        if ($child == $this->resourceClasses['search_folder']) {
            return array (['id' => $this->resourceClasses['folder'],
                'class' => $this->settings->get('resource_tree_folder_class')]
                , ['id' => $this->resourceClasses['search_folder'],
                    'class' => $this->settings->get('resource_tree_search_folder_class')]
            );
        }
        if ($child == $this->resourceClasses['document']) {
            return array (['id' => $this->resourceClasses['folder'],
                'class' => $this->settings->get('resource_tree_folder_class')]
                , ['id' => $this->resourceClasses['search_folder'],
                    'class' => $this->settings->get('resource_tree_search_folder_class')]
                , ['id' => $this->resourceClasses['document'],
                    'class' => $this->settings->get('resource_tree_document_class')]
            );
        }
        return array();
    }
    /**
     * Validate hierarchy
     * @param integer $parent
     * @param integer $child
     * @return boolean
     */
    public function validateTree ($parent, $child, $isRoot)
    {
        if ($isRoot) {
            if(strcmp(strval($child), strval($this->getFolderResourceClassId())) != 0 &&
            strcmp(strval($child), strval($this->getSearchFolderResourceClassId())) != 0) {
                return false;
            }
        }
        $correctParents = $this->getCorrectParent($child);
        foreach ($correctParents as $correctParent) {
            if (strcmp(strval($correctParent['id']), strval($parent)) == 0) {
                return true;
            }
        }
        return false;
    }
    /**
     *
     * @param integer $resourceClassId
     * @param boolean $isRelated
     * @return array
     */
    public function getItems($resourceClassId, $isRelated = false, $limit = 10000, $page = 1)
    {
        $entity = 'parent_items';
        $query = ['resource_class_id' => $resourceClassId, 'root' => true, 'per_page' => $limit, 'limit' => $limit, 'page' => $page];
        $response = $this->api->search($entity, $query);
        return $response->getContent();
    }

    public function deleteTree($itemId)
    {
        $this->api->delete('item_trees', ['parent_item_id' => $itemId]);
        $this->api->delete('item_trees', ['child_item_id' => $itemId]);
    }

    public function getRelatedItemId($id) {
        $sql = 'select
  id
  , target_resource_class_id
from
  parent_item
where
  child_item_id = ?
  and id !=  ?
order by
  depth desc ';
        $parents = $this->connection->fetchAll($sql, [$id, $id], []);
        foreach ($parents as $parent) {
            if ($parent['target_resource_class_id'] != $this->getDocumentResourceClassId()) {
                return $parent['id'];
            }
        }
        return 0;
    }

    public function createTree(Request $request)
    {
        $itemId = $request->getId();
        $this->api->delete('item_trees', ['child_item_id' => $itemId]);
        $resourceClassId = $request->getContent()['o:resource_class']['o:id'];
        $isDocument = false;
        if (strcmp($resourceClassId, strval($this->getDocumentResourceClassId())) == 0) {
            $isDocument = true;
        }
        $createTree = function ($parentItemId) use ($itemId, $resourceClassId, $isDocument) {
            $response = $this->api->read('items', ['id' => $parentItemId]);
            $parentItem = $response->getContent();
            $maxDepth = 0;
            $totalCount = $response->getTotalResults() + 1;
            $addParent = false;
            $directParent = true;
            if ($isDocument && strcmp(strval($parentItem->resourceClass()->id()), strval($this->getSearchFolderResourceClassId())) != 0) {
                $directParent = false;
            }
            // Check folder should be parent
            $parentShouldBe = 0;
            $response = $this->api->search('item_trees', ['child_item_id' => $parentItemId, 'sort_by' => 'depth', 'sort_order' => 'asc']);
            $parents = $response->getContent();
            foreach ($parents as $parent) {
                // get item
                $response = $this->api->read('items', ['id' => $parent->parentItem()->id()]);
                $item = $response->getContent();
                if (strcmp(strval($item->resourceClass()->id()), strval($this->getSearchFolderResourceClassId())) == 0) {
                    $parentShouldBe = $item->id();
                }
            }
            if (count($parents) > 0) {
                foreach($parents as $parent) {
                    $isParent = false;
                    if ($maxDepth < $parent->depth()) {
                        $maxDepth = $parent->depth();
                    }
                    if (!$directParent) {
                        if ($parentShouldBe == $parent->parentItem()->id()) {
                            $isParent = true;
                            $addParent = true;
                        }
                    } else {
                        $isParent = false;
                    }
                    $query = ['parent_item_id' => $parent->parentItem()->id(),
                        'child_item_id' => $itemId,
                        'depth' => $parent->depth(),
                        'is_here' => false,
                        'is_parent' => $isParent
                    ];
                    if ($parent->depth() > 1) {
                        $this->api->create('item_trees', $query);
                    }
                }
            } else {
                $query = ['parent_item_id' => $parentItemId,
                    'child_item_id' => $parentItemId,
                    'depth' => ++$maxDepth,
                    'is_here' => false,
                    'is_parent' => false
                ];
                $this->api->create('item_trees', $query);
            }

            $query = ['parent_item_id' => $parentItemId,
                'child_item_id' => $itemId,
                'depth' => ++$maxDepth,
                'is_here' => true,
                'is_parent' => $isDocument && !$addParent ? true: false
            ];
            $this->api->create('item_trees', $query);
        };
        foreach ($request->getContent() as $key => $values) {
            $multiParents = false;
            if (!is_array($values)) {
                continue;
            }
            foreach($values as $value) {
                if (!is_array($value)) {
                    continue;
                }
                if (!array_key_exists('type', $value)) {
                    continue;
                }
                if (strcmp($value['type'], 'resource') != 0 && strcmp($value['type'], 'resource:item') != 0) {
                    continue;
                }
                if (!array_key_exists('value_resource_id', $value)) {
                    continue;
                }
                if (empty($value['value_resource_id'])) {
                    continue;
                }
                $parentResourceClassId = $this
                ->getItemResourceClassId($value['value_resource_id']);
                if ($parentResourceClassId == -1) {
                    continue;
                }
                $parentItemId = $value['value_resource_id'];
                $createTree($parentItemId);
                break;
            }
        }
    }
}