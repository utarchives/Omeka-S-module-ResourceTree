<?php
namespace ResourceTree\Site\Navigation\Link;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;

class ResourceTreeBrowse implements LinkInterface
{
    public function getName()
    {
        return 'Browse resource tree'; // @translate
    }

    public function getFormTemplate()
    {
        return 'common/navigation-link-form/resource-tree-browse';
    }

    public function isValid(array $data, ErrorStore $errorStore)
    {
        return true;
    }

    public function getLabel(array $data, SiteRepresentation $site)
    {
        return isset($data['label']) && '' !== trim($data['label'])
        ? $data['label'] : $this->getName();
    }

    public function toZend(array $data, SiteRepresentation $site)
    {
        return [
            'route' => 'site/resource-tree',
            'params' => [
                'site-slug' => $site->slug(),
            ],
        ];
    }

    public function toJstree(array $data, SiteRepresentation $site)
    {
        return [
            'label' => $data['label'],
        ];
    }
}
