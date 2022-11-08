<?php
namespace ResourceTree\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class TreeListNolink extends AbstractHelper {
    public function __invoke($id, $title)
    {
        $view = $this->getView();
        $response = $view->api()->search('parent_items', ['child_item_id' => $id, 'sort_by' => 'depth', 'sort_order' => 'asc']);
        $items = $response->getContent();
        $count = 1;
        foreach ($items as $item) {
            if ($item->id() == $id) {
                continue;
            }
            echo $item->item()->displayTitle();
            if (count($items) > $count + 1) {
                echo '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
            }

            $count++;
        }
        echo !empty(trim($title)) ?  '&nbsp;&nbsp;&gt;&nbsp;&nbsp;' . $title : '';
    }


}