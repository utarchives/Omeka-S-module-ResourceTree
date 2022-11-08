<?php
namespace ResourceTree\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;

class RootItemRepresentation extends AbstractEntityRepresentation
{

    /**
     * Get Item.
     *
     * @return ItemRepresentation
     */
    public function resourceClassId()
    {
        return $this->resource->getResourceClassId();
    }

    public function getJsonLdType()
    {
        return 'o:ItemTree';

    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id,
            'o:resource_class_id' => $this->resourceClassId()
        ];
    }

}