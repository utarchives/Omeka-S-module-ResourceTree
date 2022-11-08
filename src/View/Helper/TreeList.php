<?php
namespace ResourceTree\View\Helper;

use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;
use Laminas\View\Helper\AbstractHelper;

class TreeList extends AbstractHelper {
    /**
     *
     * @var RelatedItemsData
     */
    protected $relatedItemsData;
    public function __construct(RelatedItemsData $relatedItemsData)
    {
        $this->relatedItemsData = $relatedItemsData;
    }
    public function __invoke($id)
    {
        $view = $this->getView();
        $response = $view->api()->search('parent_items', ['child_item_id' => $id, 'sort_by' => 'depth', 'sort_order' => 'asc']);
        $items = $response->getContent();
        $count = 1;
        foreach ($items as $item) {
            if ($item->id() == $id) {
                continue;
            }
            if ($count == 1) {
                $count++;
                continue;
            }
            $url = '<a href="'. $item->item()->siteUrl() . '">';
            if ($item->item()->resourceClass()->id() == $this->relatedItemsData->getDocumentResourceClassId()) {
                echo $url;
                // echo'<i class="fa fa-file-o" aria-hidden="true"></i>';
            } else {
//                 $targetUrl = function ($url, $checkString) {
//                     $returnUrl = $url;
//                     $path = explode('/', $url);
//                     $targetPath = $path[count($path) - 1];
//                     if (strcmp($targetPath, $checkString) != 0) {
//                         $returnUrl = str_replace('/' . $targetPath, '', $url);
//                     }
//                     return $returnUrl;
//                 };
//                 $url = $targetUrl($view->url('site/docarchive-id', ['id' => $item->id()], [], true), $item->id());
//                 echo '<a href="' . $url . '">';
                echo $url;
                // echo'<i class="fa fa-folder-open-o" aria-hidden="true"></i>';
            }
            echo $item->item()->displayTitle();
            echo '</a>';
            if (count($items) > $count + 1) {
                echo '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
            }
            $count++;
        }
    }


}