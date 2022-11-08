<?php
namespace ResourceTree\Job;

use Omeka\Job\AbstractJob;

class CreateTreeByLinkedResource extends AbstractJob
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
        $this->createTreeByLinkedResource();
    }
    protected function createTreeByLinkedResource() {
        $response = $this->api->search('item_trees', [], ['returnScalar' => 'id']);
        $this->api->batchDelete('item_trees', $response->getContent());
        $response = $this->api->search('root_items', []);
        $rootItems = $response->getContent();
        $create = function ($items, $isRoot, $depth, $generation) use (&$create) {
            foreach($items as $item) {
                if (!$isRoot) {
                    $generation[$depth] = $item->parentItemId();
                    $directParent = true;
                    if (strcmp($item->resourceClassId(), strval($this->searchFolderClassId)) != 0) {
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
                            'child_item_id' => $item->id(),
                            'depth' => $i,
                            'is_here' => $isHere,
                            'is_parent' => $isParent
                        ];
                        $this->api->create('item_trees', $query);
                    }
                    $query = ['parent_item_id' => $item->id(),
                        'child_item_id' => $item->id(),
                        'depth' => $depth + 1,
                        'is_here' => 0,
                        'is_parent' => $directParent ? $directParent : 0
                    ];
                    $this->api->create('item_trees', $query);
                } else {
                    $isParent = 0;
                    if (strcmp(strval($item->resourceClassId()), $this->searchFolderClassId) == 0) {
                        $isParent = true;
                    }
                    $depth = 1;
                    $generation = [];
                    $parentId = $item->id();
                    $query = ['parent_item_id' => $parentId,
                        'child_item_id' => $item->id(),
                        'depth' =>1,
                        'is_here' => true,
                        'is_parent' => $isParent
                    ];
                    $this->api->create('item_trees', $query);
                }
                $response = $this->api->search('linked_items', ['parent_item_id' => $item->id()]);
                $linkedItems = $response->getContent();
                if (count($linkedItems) > 0) {
                    $nextDepth = $depth + 1;
                    $create($linkedItems, false, $nextDepth, $generation);
                }
            }
            return true;
        };
        $depth = 0;
        $generation = [];
        $create($rootItems, true, $depth, $generation);
    }
}

