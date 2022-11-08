<?php
namespace ResourceTree\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ResourceClassRepresentation;
use Omeka\Api\Representation\ResourceTemplateRepresentation;
use Omeka\Api\Representation\ValueRepresentation;

class ParentItemRepresentation extends AbstractEntityRepresentation
{
    /**
     * Get Item.
     *
     * @return ItemRepresentation
     */
    public function item()
    {
        return $this->getAdapter('items')
        ->getRepresentation($this->resource->getItem());
    }
    /**
     * Get the resource class representation of this resource.
     *
     * @return ResourceClassRepresentation
     */
    public function resourceClass()
    {
        return $this->getAdapter('resource_classes')
        ->getRepresentation($this->resource->getResourceClass());
    }
    public function targetResourceClassId()
    {
        return $this->resource->getTargetResourceClassId();
    }
    public function title()
    {
        return $this->resource->getTitle();
    }
    /**
     * Get the resource template of this resource.
     *
     * @return ResourceTemplateRepresentation
     */
    public function resourceTemplate()
    {
        return $this->getAdapter('resource_templates')
        ->getRepresentation($this->resource->getResourceTemplate());
    }
    /**
     * Get all value representations of this resource.
     *
     * <code>
     * array(
     *   {term} => array(
     *     'property' => {PropertyRepresentation},
     *     'alternate_label' => {label},
     *     'alternate_comment' => {comment},
     *     'values' => array(
     *       {ValueRepresentation},
     *       {ValueRepresentation},
     *     ),
     *   ),
     * )
     * </code>
     *
     * @return array
     */
    public function values()
    {
        if (isset($this->values)) {
            return $this->values;
        }

        // Set the default template info.
        $templateInfo = [
            'dcterms:title' => [],
            'dcterms:description' => [],
        ];

        $template = $this->resourceTemplate();
        if ($template) {
            // Set the custom template info.
            $templateInfo = [];
            foreach ($template->resourceTemplateProperties() as $templateProperty) {
                $term = $templateProperty->property()->term();
                $templateInfo[$term] = [
                    'alternate_label' => $templateProperty->alternateLabel(),
                    'alternate_comment' => $templateProperty->alternateComment(),
                ];
            }
        }

        // Get this resource's values.
        $values = [];
        foreach ($this->resource->getValues() as $valueEntity) {
            $value = new ValueRepresentation($valueEntity, $this->getServiceLocator());
            if ('resource' === $value->type() && null === $value->valueResource()) {
                // Skip this resource value if the resource is not available
                // (most likely becuase it is private).
                continue;
            }
            $term = $value->property()->term();
            if (!isset($values[$term]['property'])) {
                $values[$term]['property'] = $value->property();
                $values[$term]['alternate_label'] = null;
                $values[$term]['alternate_comment'] = null;
            }
            $values[$term]['values'][] = $value;
        }

        // Order this resource's values according to the template order.
        $sortedValues = [];
        foreach ($values as $term => $valueInfo) {
            foreach ($templateInfo as $templateTerm => $templateAlternates) {
                if (array_key_exists($templateTerm, $values)) {
                    $sortedValues[$templateTerm] =
                    array_merge($values[$templateTerm], $templateAlternates);
                }
            }
        }

        $this->values = $sortedValues + $values;
        return $this->values;
    }
    public function getJsonLdType()
    {
        return 'o:ChildItem';

    }

    public function getJsonLd()
    {
        return [
            'o:id' => $this->id,
            'o:item' => $this->item()->getReference(),
        ];
    }

}