<?php
namespace ResourceTree\Controller\Site;

use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;
use ResourceTree\Mvc\Controller\Plugin\ResourceTreeGenerator;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

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
    public function __construct(ResourceTreeGenerator $resourceTree, RelatedItemsData $relatedItemsData)
    {
        $this->resourceTree = $resourceTree;
        $this->relatedItemsData = $relatedItemsData;
    }
    public function browseAction()
    {
        $query = $this->params()->fromQuery();
        $itemIdentifier = '';
        if (isset($query['id'])) {
            $itemIdentifier = $query['id'];
        }
        // Get all markers in this site's item pool and render them on a map.
        $site = $this->currentSite();
        $this->setBrowseDefaults('title');
        $view = new ViewModel();
//         $tree = $this->resourceTree->getJsTree(true);
//         $treeUri = $this->url()->fromRoute('site/get-resource-tree', ['site-slug' => $this->currentSite()->slug()], [], false);
//         $view->setVariable('linkTree', $treeUri);
        $view->setVariable('site', $site);
        $view->setVariable('defaultDepth', $this->relatedItemsData->getTargetDefaultDepth());
        $view->setVariable('folderClass', $this->relatedItemsData->getFolderResourceClassId());
        $view->setVariable('searchFolderClass', $this->relatedItemsData->getSearchFolderResourceClassId());
        $view->setVariable('documentClass', $this->relatedItemsData->getDocumentResourceClassId());
        $view->setVariable('itemIdentifier', $itemIdentifier);
        return $view;
    }
    public function redirectAction() {
        $id = $this->params('id');
        $response = $this->api()->read('items', ['id' => $this->params('id')]);
        $item = $response->getContent();
        $this->redirect()->toUrl($item->siteUrl());
    }
    public function resourceTreeAction()
    {
//         $response = $this->api()->searchOne('json_item_trees', []);
        $filePath = OMEKA_PATH . '/files/resource-tree/';
        $fileName = 'resource-tree.json';
        $linkTree = file_get_contents($filePath . $fileName);
        $view = new ViewModel();
        header('Content-Type: text/plain');
        echo $linkTree;
        exit;
    }
}