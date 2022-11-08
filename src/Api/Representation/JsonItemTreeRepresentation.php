<?php
namespace ResourceTree\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;

class JsonItemTreeRepresentation extends AbstractEntityRepresentation
{
    public function itemTree()
    {
        return $this->resource->getItemTree();
    }

    public function getJsonLdType()
    {
        return 'o:JsonItemTree';

    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id,
            'o:item_tree' => $this->itemTree(),
        ];
    }

}