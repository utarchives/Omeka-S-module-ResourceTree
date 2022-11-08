<?php
namespace ResourceTree\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;

class LinkedItemRepresentation extends AbstractEntityRepresentation
{

    /**
     * Get Item.
     *
     * @return int
     */
    public function resourceClassId()
    {
        return $this->resource->getResourceClassId();
    }
    /**
     * Get Item.
     *
     * @return int
     */
    public function parentResourceClassId()
    {
        return $this->resource->getParentResourceClassId();
    }
    /**
     * Get Item.
     *
     * @return int
     */
    public function parentItemId()
    {
        return $this->resource->getParentItemId();
    }

    public function getJsonLdType()
    {
        return 'o:ItemTree';

    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id,
            'o:resource_class_id' => $this->resourceClassId(),
            'o:parent_item_id' => $this->parentItemId(),
            'o:resource_class_id' => $this->parentResourceClassId(),
        ];
    }

}