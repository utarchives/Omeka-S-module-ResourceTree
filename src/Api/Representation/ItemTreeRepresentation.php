<?php
namespace ResourceTree\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;

class ItemTreeRepresentation extends AbstractEntityRepresentation
{

    /**
     * Get Item.
     *
     * @return ItemRepresentation
     */
    public function parentItem()
    {
        return $this->getAdapter('items')
        ->getRepresentation($this->resource->getParentItem());
    }
    /**
     * Get Item.
     *
     * @return ItemRepresentation
     */
    public function childItem()
    {
        return $this->getAdapter('items')
        ->getRepresentation($this->resource->getChildtIem());
    }

    public function depth()
    {
        return $this->resource->getDepth();
    }

    public function isHere()
    {
        return $this->resource->getIsHere();
    }

    public function isParent()
    {
        return $this->resource->getIsParent();
    }

    public function getJsonLdType()
    {
        return 'o:ItemTree';

    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id,
            'o:child_item' => $this->childItem()->getReference(),
            'o:parent_item' => $this->parentItem()->getReference(),
            'o:depth' => $this->depth(),
            'o:is_here' => $this->isHere(),
        ];
    }

}