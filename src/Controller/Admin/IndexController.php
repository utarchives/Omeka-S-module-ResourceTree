<?php

namespace ResourceTree\Controller\Admin;

use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use ResourceTree\Form\ResourceTreeForm;
use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;
use ResourceTree\Mvc\Controller\Plugin\ResourceTreeGenerator;


class IndexController extends AbstractActionController
{

    /**
     * @var ResourceTreeGenerator
     */
    protected $resourceTree;
    /**
     *
     * @var RelatedItemsData
     */
    protected $relatedItemsData;
    const PROPERTY_ERROR = 'Property Error';
    const TREE_ERROR = 'Tree Error';
    const DUPLICATE_ERROR = 'Duplicate Error';
    protected $error;

    public function __construct(ResourceTreeGenerator $resourceTree, RelatedItemsData $relatedItemsData)
    {
        $this->resourceTree = $resourceTree;
        $this->relatedItemsData = $relatedItemsData;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Laminas\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->setBrowseDefaults('title');
        $form = $this->getForm(ResourceTreeForm::class);
        if ($this->getRequest()->isPost()) {
            $formData = $this->params()->fromPost();
            $tree = json_decode($formData['jstree'], true);
//             $tree = $this->resourceTree->fromJstree($jstree);
//             $form->setData($formData);
            if ($this->validateTree($tree)) {
                $param['tree'] = $tree;
                $param['documentClassId'] = $this->relatedItemsData->getDocumentResourceClassId();
                $param['searchFolderClassId'] = $this->relatedItemsData->getSearchFolderResourceClassId();
                $dispatcher = $this->jobDispatcher();
                $job = $dispatcher->dispatch('ResourceTree\Job\UpdateResourceTree', $param);
                $this->messenger()->addSuccess('Updating Tree in Job ID ' . $job->getId()); // @translate
                return $this->redirect()->toUrl($this->url()->fromRoute('admin/resource-tree/finish', [], ['query' => 'id=' . $job->getId()], false));
            } else {
                $errorMessage = '';
                $response = $this->api()->read('items', $this->error['label']);

                if (strcmp($this->error['type'], self::TREE_ERROR) == 0) {
                    $errorMessage = $response->getContent()->displayTitle() .' should follow ' . $this->error['parent']; // @translate
                } else if (strcmp($this->error['type'], self::PROPERTY_ERROR) == 0) {
                    $errorMessage = $response->getContent()->displayTitle() . ' should select property. '; // @translate
                } else {
                    $errorMessage = $response->getContent()->displayTitle() . ' is duplicated. '; // @translate
                }
                $this->messenger()->addError($errorMessage); // @translate
            }
        }
        $view = new ViewModel();
        $view->form = $form;
        $tree = $this->resourceTree->getAdminJsTree();
        $view->setVariable('linkTree', $tree);
        // not related items
//         $view->setVariable('folderItems', $folderItems);
//         $view->setVariable('documentItems', $documentItems);
//         $view->setVariable('imageItems', $imageItems);
        // resource classes
        $view->setVariable('folderClass', $this->settings()->get('resource_tree_folder_class'));
        $view->setVariable('documentClass', $this->settings()->get('resource_tree_document_class'));
        return $view;
    }

    public function updateTreeAction() {
        $this->setBrowseDefaults('title');
        $form = $this->getForm(ResourceTreeForm::class);
        if ($this->getRequest()->isPost()) {
            $filePath = OMEKA_PATH . '/files/resource-tree/';
            $fileName = 'resource-tree.json';
            if (!file_exists($filePath)) {
                mkdir($filePath);
            }
            $response = $this->api()->search('sites', []);
            $sites = $response->getContent();
            $result = true;
            foreach ($sites as $site) {
                $slug = $site->slug();
                $tree = $this->resourceTree->createJsonItemTree($site->itemPool(), false);
                $allFileName = $slug . '-all-' . $fileName;
                if(!file_put_contents($filePath . $allFileName, json_encode($tree))) {
                    $result = false;
                }
                $tree = $this->resourceTree->createJsonItemTree($site->itemPool(), true);
                $publicFileName = $slug . '-public-' . $fileName;
                if(!file_put_contents($filePath . $publicFileName, json_encode($tree))) {
                    $result = false;
                }
            }
            if (!$result) {
                $this->messenger()->addError('Failed to Recreate Tree Display'); // @translate

            } else {
                $this->messenger()->addSuccess('Recreated Tree Display'); // @translate
            }
        }
        $view = new ViewModel();
        $view->form = $form;
        return $view;
    }
    /**
     *
     */
    public function finishAction()
    {
        $response = $this->api()->read('jobs', ['id' => $this->params()->fromQuery('id')]);
        $view = new ViewModel();
        $view->setVariable('job', $response->getContent());
        return $view;
    }
    public function createTreeAction() {
        $this->setBrowseDefaults('title');
        $form = $this->getForm(ResourceTreeForm::class);
        if ($this->getRequest()->isPost()) {
            $param['documentClassId'] = $this->relatedItemsData->getDocumentResourceClassId();
            $param['searchFolderClassId'] = $this->relatedItemsData->getSearchFolderResourceClassId();
            $dispatcher = $this->jobDispatcher();
            $job = $dispatcher->dispatch('ResourceTree\Job\CreateTreeByLinkedResource', $param);
            $this->messenger()->addSuccess('Creating Tree in Job ID ' . $job->getId()); // @translate
            return $this->redirect()->toUrl($this->url()->fromRoute('admin/resource-tree/finish', [], ['query' => 'id=' . $job->getId()], false));
        }
        $view = new ViewModel();
        $view->form = $form;
        return $view;
    }
    protected function createTreeByLinkedResource() {
        echo '<pre>';
        $response = $this->api()->search('item_trees', [], ['returnScalar' => 'id']);
        $this->api()->batchDelete('item_trees', $response->getContent());
        $response = $this->api()->search('root_items', []);
        $rootItems = $response->getContent();
        $create = function ($items, $isRoot, $depth, $generation) use (&$create) {
            foreach($items as $item) {
                if (!$isRoot) {
                    $generation[$depth] = $item->parentItemId();
                    $directParent = true;
                    if (strcmp($item->resourceClassId(), strval($this->relatedItemsData->getDocumentResourceClassId())) == 0) {
                        $directParent = false;
                    }
                    $parentShouldBe = 0;
                    for ($i = 2; $i <= $depth; $i++) {
                        // get item
                        $response = $this->api()->read('items', ['id' => $generation[$i]]);
                        $parentItem = $response->getContent();
                        if (strcmp(strval($parentItem->resourceClass()->id()), $this->relatedItemsData->getSearchFolderResourceClassId()) == 0) {
                            $parentShouldBe = $parentItem->id();
                        }
                        var_dump($item->parentResourceClassId());
                    }
                    var_dump($parentShouldBe . "////" . $item->id());
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
                        $this->api()->create('item_trees', $query);
                    }
                    $query = ['parent_item_id' => $item->id(),
                        'child_item_id' => $item->id(),
                        'depth' => $depth + 1,
                        'is_here' => 0,
                        'is_parent' => $directParent ? $directParent : 0
                    ];
                    $this->api()->create('item_trees', $query);
                } else {
                    $depth = 1;
                    $generation = [];
                    $parentId = $item->id();
                    $query = ['parent_item_id' => $parentId,
                        'child_item_id' => $item->id(),
                        'depth' =>1,
                        'is_here' => true,
                        'is_parent' => 0
                    ];
                    $this->api()->create('item_trees', $query);
                }
                $response = $this->api()->search('linked_items', ['parent_item_id' => $item->id()]);
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
    /**
     *
     * @param $tree
     * @return boolean
     */
    protected function validateTree($tree) {
        $currentClass = $this->relatedItemsData->getFolderResourceClassId();
        $registeredItems = [];
        $validation = function ($tree, $currentClass, $isRoot) use (&$validation, &$registeredItems) {
            foreach($tree as $item) {
                // check hierarchy
                if (!$this->relatedItemsData->validateTree($currentClass, $item['type'], $isRoot)) {
                    $parent = "";
                    $count = count($this->relatedItemsData->getCorrectParent($item['type']));
                    for ($i = 0; $i < $count; $i++) {
                        $parent .= $this->relatedItemsData->getCorrectParent($item['type'])[$i]['class'];
                        if ($i < $count - 1){
                            $parent .= ' or ';
                        }
                    }
                    $response = $this->api()->read('items', $item['data']['id']);
                    $this->error = array ('type' => self::TREE_ERROR,
                        'label' => $item['data']['id'],
                        'parent' => $parent);
                    return false;
                }
                // no property
                if (!$isRoot && empty($item['data']['property'])) {
                    $this->error = array ('type' => self::PROPERTY_ERROR, 'label' => $item['data']['id']);
                    return false;
                }
                if (isset($registeredItems[$item['data']['id']])) {
                    $this->error = array ('type' => self::DUPLICATE_ERROR, 'label' => $item['data']['id']);
                    return false;
                } else {
                    $registeredItems[$item['data']['id']] = $item['data']['id'];
                }
                if (count($item['links']) > 0) {
                    if (!$validation($item['links'], $item['type'], false)) {
                        return false;
                    }
                }
            }
            return true;
        };
        $result = $validation($tree, $currentClass, true);
        if (!$result) {
            return false;
        }
        return true;
    }
    /**
     * appearnce
     * @return \Laminas\View\Model\ViewModel
     */
    public function resourceTreeLinkFormAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/resource-tree-form/property-select');
        $view->setVariable('data', $this->params()->fromPost('data'));
        return $view;
    }
    public function sidebarSelectAction()
    {
        $this->setBrowseDefaults('created');
        $params = $this->params()->fromQuery();
        $params['sort_by'] = 'resource_class_id';
        $params['sort_order'] = 'asc';
        $response = $this->api()->search('not_related_items', $params);
//         $response = $this->api()->search('items', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));

        $view = new ViewModel;
        $view->setVariable('items', $response->getContent());
        $view->setVariable('search', $this->params()->fromQuery('search'));
        $view->setVariable('resourceClassId', $this->params()->fromQuery('resource_class_id'));
        $view->setVariable('itemSetId', $this->params()->fromQuery('item_set_id'));
        $view->setVariable('showDetails', false);
        $view->setTerminal(true);
        return $view;
    }
}
