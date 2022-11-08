<?php
namespace ResourceTree\Job;

use Omeka\Job\AbstractJob;

class UpdateResourceTree extends AbstractJob
{
    protected $api;

    protected $addedCount;

    protected $logger;

    protected $hasErr = false;

    protected $documentClassId;

    protected $searchFolderClassId;

    protected $resourceTree;
    public function perform()
    {
        ini_set("auto_detect_line_endings", true);
        $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
        $this->api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $config = $this->getServiceLocator()->get('Config');
        $controllerPlugins = $this->getServiceLocator()->get('ControllerPluginManager');
        $this->documentClassId = $this->getArg('documentClassId');
        $this->searchFolderClassId = $this->getArg('searchFolderClassId');
        $this->resourceTree = $controllerPlugins->get('resourceTreeGenerator');
        $tree = $this->getArg('tree');
        $this->updateTree($tree);
    }
    /**
     *
     * @param array $tree
     * @return boolean
     */
    protected function updateTree($tree)
    {
        $update = function ($tree) {
            $depth = 0;
            $generation = [];
            $this->api->delete('parent_items', []);
            $this->deleteItemTrees();
            $create = function ($tree, $parentId, $isRoot, $depth, $generation) use (&$create) {
                foreach($tree as $item) {
                    if ($item['remove']) {
                        continue;
                    }
                    if (!$isRoot) {

                        $query = ['id' => $item['data']['property']];
                        $response = $this->api->read('properties', $query);
                        $property = $response->getContent();
                        // リソースの公開状況を取得する
                        $resource = $this->api->read('resources', ['id' => $item['data']['id']]);
                        $resourceData = $resource->getContent();
                        // 公開状況を追加
                        $query = ['property_id' => $item['data']['property'],
                            'resource_id' => $item['data']['id'],
                            'type' => 'resource',
                            'value_resource_id' => $parentId,
                            'is_public' => (integer)$resourceData->isPublic
                        ];
                        $response =  $this->api->create('parent_items', $query);
                        $generation[$depth] = $parentId;
                        $directParent = true;
                        // get parent
                        $response = $this->api->read('items', ['id' => $parentId]);
                        $parentItem = $response->getContent();
                        if (strcmp($item['type'], strval($this->searchFolderClassId)) != 0) {
                                $directParent = false;
                        }
                        $parentShouldBe = 0;
                        for ($i = 2; $i <= $depth; $i++) {
                            // get item
                            $response = $this->api->read('items', ['id' => $generation[$i]]);
                            $parentItem = $response->getContent();
                            if (strcmp(strval($parentItem->resourceClass()->id()), $this->searchFolderClassId) == 0) {
                                $parentShouldBe = $parentItem->id();
                            }
                        }
                        for ($i = 2; $i <= $depth; $i++) {
                            $isHere = 0;
                            $isParent = 0;
                            if ($i == $depth) {
                                $isHere = true;
                            }
                            if ($directParent) {
                                $isParent = 0;
                            } else {
                                if ($generation[$i] == $parentShouldBe) {
                                    $isParent = true;
                                }
                            }
                            $query = ['parent_item_id' => $generation[$i],
                                'child_item_id' => $item['data']['id'],
                                'depth' => $i,
                                'is_here' => $isHere,
                                'is_parent' => $isParent
                            ];
                            $this->api->create('item_trees', $query);
                        }

                        $query = ['parent_item_id' => $item['data']['id'],
                            'child_item_id' => $item['data']['id'],
                            'depth' => $depth + 1,
                            'is_here' => 0,
                            'is_parent' => $directParent ? $directParent : 0
                        ];
                        $this->api->create('item_trees', $query);
                    } else {
                        $response = $this->api->read('items', ['id' => $item['data']['id']]);
                        $parentItem = $response->getContent();
                        $isParent = 0;
                        if (strcmp(strval($parentItem->resourceClass()->id()), $this->searchFolderClassId) == 0) {
                            $isParent = true;
                        }
                        $depth = 1;
                        $generation = [];
                        $parentId = $item['data']['id'];
                        $query = ['parent_item_id' => $parentId,
                            'child_item_id' => $item['data']['id'],
                            'depth' =>1,
                            'is_here' => true,
                            'is_parent' => $isParent
                        ];
                        $this->api->create('item_trees', $query);
                    }
                    if (count($item['links']) > 0) {
                        $nextDepth = $depth + 1;
                        $create($item['links'], $item['data']['id'], false, $nextDepth, $generation);
                    }
                }
                return true;
            };
            $create($tree, 0, true, $depth, $generation);
        };
        $update($tree);
    }
    protected function deleteItemTrees() {
        $this->api->delete('item_trees', []);
    }
}

