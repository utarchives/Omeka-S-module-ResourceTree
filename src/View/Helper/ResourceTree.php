<?php
namespace ResourceTree\View\Helper;

use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;
use ResourceTree\Mvc\Controller\Plugin\ResourceTreeGenerator;
use Laminas\View\Helper\AbstractHelper;

class ResourceTree extends AbstractHelper {
    /**
     * @var ResourceTreeGenerator
     */
    protected $resourceTree;
    /**
     *
     * @var RelatedItemsData
     */
    protected $relatedItemsData;
    protected $targetProperty;
    protected $targetController;
    protected $settings;
    public function __construct(ResourceTreeGenerator $resourceTree, RelatedItemsData $relatedItemsData, $targetProperty, $targetController, $settings)
    {
        $this->resourceTree = $resourceTree;
        $this->relatedItemsData = $relatedItemsData;
        $this->targetProperty = $targetProperty;
        $this->targetController = $targetController;
        $this->settings = $settings;
    }

    public function __invoke()
    {
        return $this;
    }
    public function getFullTree() {
        $view = $this->getView();
        return $view->url('site/resource-tree', [], [], true);
    }
    public function getItemUrl() {
        $view = $this->getView();
        $controller = 'item';
        if (!empty($this->targetController)) {
            $controller = $this->targetController;
        }
        $itemUrl = $view->url('site', [], [], true);
        $itemUrl .= '/resource-tree';
//         if (empty($this->targetProperty)) {
//             $itemUrl .= '/item';
//         } else {
//             $target = $this->targetController;
//             if (strcmp(substr($target, strlen($target) - 1, 1), '/') == 0) {
//                 $target = substr($target, 0, strlen($target) - 1);
//             }
//             $itemUrl .= '/' . $target;
//         }
//         $itemUrl = $targetUrl($view->url('site/resource', ['controller' => 'item'], [], true), $controller);
        return $itemUrl;
    }

    public function getIdentifier($item) {
        $view = $this->getView();
        if ($this->settings->get('resource_tree_with_document_class', true)) {
            return $item->id();
        }
        if($item->ResourceClass()->id() != $this->relatedItemsData->getDocumentResourceClassId()) {
            return $item->id();
        } else {
            return $this->relatedItemsData->getRelatedItemId($item->id());
        }
    }
    public function getLinkTree($slug, $identity) {
//         $url = 'http://';
//         if (isset($_SERVER['HTTPS'])) {
//             $url = 'https://';
//         }
//         $url .= $_SERVER['HTTP_HOST'];
        $view = $this->getView();
        $url =  $view->url('api', [], [], true);
        $url = substr($url, 0, strlen($url) - 3);
        $url .= 'files/resource-tree/%s-%s-resource-tree.json?' . date('Ymdhsi');
        $target = 'all';
        if (!$identity) {
            $target = 'public';
        }
        $url = sprintf($url, $slug, $target);
        return $url;
//         $view = $this->getView();
//         return $view->url('site/get-resource-tree', [], [], true);
    }
    public function getDefaultDepth() {
        return $this->relatedItemsData->getTargetDefaultDepth();
    }
    public function getFolderResourceClassId() {
        return $this->relatedItemsData->getFolderResourceClassId();
    }
    public function getSearchFolderResourceClassId() {
        return  $this->relatedItemsData->getSearchFolderResourceClassId();
    }
    public function getDocumentResourceClassId() {
        return $this->relatedItemsData->getDocumentResourceClassId();
    }
}