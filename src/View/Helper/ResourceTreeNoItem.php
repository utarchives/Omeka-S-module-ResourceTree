<?php
namespace ResourceTree\View\Helper;

use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;
use ResourceTree\Mvc\Controller\Plugin\ResourceTreeGenerator;
use Laminas\View\Helper\AbstractHelper;

class ResourceTreeNoItem extends AbstractHelper {
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

    public function __invoke()
    {
        $view = $this->getView();
        $view->headScript()->appendFile($view->assetUrl('vendor/jstree/jstree.min.js', 'Omeka'));
        $view->headScript()->appendFile($view->assetUrl('js/resource-tree.js', 'ResourceTree'));
        $loadingImage = $view->assetUrl('css/throbber.gif', 'ResourceTree');
        // $view->headLink()->appendStylesheet($view->assetUrl('css/jstree.css', 'Omeka'));
        $escapeHtml = $view->plugin('escapeHtml');
        $outputHtml = <<<'HTML'
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/themes/default/style.min.css" />
<div id="block-window">
	<div id="tree-header">資料群階層&nbsp;<a href="%s" id="tree-browse">拡大表示</a></div>
    <div id="item-set-tree"
        data-item-url="%s"
        data-default-depth="%s"
        data-jstree-data="%s"
        data-folder-class="%s"
        data-search-folder-class="%s"
        data-search-document-class="%s">
<img src="%s">&nbsp;Loading...
    </div>
</div>
HTML;
        $targetUrl = function ($url, $checkString) {
            $returnUrl = $url;
            $path = explode('/', $url);
            $targetPath = $path[count($path) - 1];
            if (strcmp($targetPath, $checkString) != 0) {
                $returnUrl = str_replace('/' . $targetPath, '', $url);
            }
            return $returnUrl;
        };

        $linkTree = $view->url('site/get-resource-tree', [], [], true);
        $url = $targetUrl($view->url('site/resource', ['controller' => 'item'], [], true), 'item');
//         $childrenUrl = $targetUrl($view->url('site/docarchive', [], [], true), 'docarchive');
        $outputHtml = sprintf($outputHtml,
            $view->url('site/resource-tree', [], [], true),
            $url,
            $this->relatedItemsData->getTargetDefaultDepth(),
            $linkTree,
            $this->relatedItemsData->getFolderResourceClassId(),
            $this->relatedItemsData->getSearchFolderResourceClassId(),
            $this->relatedItemsData->getDocumentResourceClassId(),
            $loadingImage);
        echo $outputHtml;
    }

}