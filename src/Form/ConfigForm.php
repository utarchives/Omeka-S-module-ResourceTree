<?php

namespace ResourceTree\Form;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Omeka\Form\Element\ResourceClassSelect;
use Laminas\Form\Element\Text;

class ConfigForm extends Form  implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;
    public function init()
    {
        $this->add([
            'name' => 'resource_tree_config',
            'type' => Fieldset::class,
            'options' => [
                'label' => 'Rersource Tree Config', // @translate
                'info' => $this->translate('Setting for resource tree.') // @translate
            ],
        ]);
        $resourceTreeConfigFieldset = $this->get('resource_tree_config');
        $resourceTreeConfigFieldset->add([
            'name' => 'resource_tree_folder_class',
            'type' => ResourceClassSelect::class,
            'options' => [
                'label' => 'ResourceClass to use for folder (not searched)', // @translate
                'info' => 'This configures resource class for folder item (not searched).', // @translate
                'empty_option' => 'Select a resource tree...', // @translate
                'term_as_value' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a resource class', // @translate
            ],
        ]);
        $resourceTreeConfigFieldset->add([
            'name' => 'resource_tree_search_folder_class',
            'type' => ResourceClassSelect::class,
            'options' => [
                'label' => 'ResourceClass to use for search folder', // @translate
                'info' => 'This configures resource class for search folder item.', // @translate
                'empty_option' => 'Select a resource class', // @translate
                'term_as_value' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a resource class', // @translate
            ],
        ]);
        $resourceTreeConfigFieldset->add([
            'name' => 'resource_tree_document_class',
            'type' => ResourceClassSelect::class,
            'options' => [
                'label' => 'ResourceClass to use for document', // @translate
                'info' => 'This configures resource class for document item.', // @translate
                'empty_option' => 'Select a resource class', // @translate
                'term_as_value' => true,
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select a resource class', // @translate
            ],
        ]);
//         $resourceTreeConfigFieldset->add([
//             'name' => 'resource_tree_image_class',
//             'type' => ResourceClassSelect::class,
//             'options' => [
//                 'label' => 'ResourceClass to use for image', // @translate
//                 'info' => 'This configures resource class for image item.', // @translate
//                 'empty_option' => 'Select a resource class', // @translate
//                 'term_as_value' => true,
//             ],
//             'attributes' => [
//                 'class' => 'chosen-select',
//                 'data-placeholder' => 'Select a resource class', // @translate
//             ],
//         ]);
//         $resourceTreeConfigFieldset->add([
//             'name' => 'resource_tree_validation_check',
//             'type' => Checkbox::class,
//             'options' => [
//                 'label' => 'Validate resource tree of item edit page', // @translate
//                 'info' => 'If checked validate tree hierachy on item edit.', // @translate
//             ],
//         ]);
        $resourceTreeConfigFieldset->add([
            'name' => 'resource_tree_default_depth',
            'type' => Text::class,
            'options' => [
                'label' => 'Default Depth for opening', // @translate
                'info' => 'This configures default depth for opening hierarchy .', // @translate
            ],
        ]);
        $resourceTreeConfigFieldset->add([
            'name' => 'resource_tree_with_document_class',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Create Tree With Document Class', // @translate
                'info' => 'This configures default depth for opening hierarchy .', // @translate
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $resourceTreeFilter = $inputFilter->get('resource_tree_config');
        $resourceTreeFilter->add([
            'name' => 'resource_tree_folder_class',
            'required' => false,
        ]);
        $resourceTreeFilter->add([
            'name' => 'resource_tree_search_folder_class',
            'required' => false,
        ]);
        $resourceTreeFilter->add([
            'name' => 'resource_tree_document_class',
            'required' => false,
        ]);

//         $resourceTreeFilter->add([
//             'name' => 'resource_tree_validation_check',
//             'required' => false,
//         ]);
        $resourceTreeFilter->add([
            'name' => 'resource_tree_default_depth',
            'required' => false,
        ]);
        $resourceTreeFilter->add([
            'name' => 'resource_tree_with_document_class',
            'required' => false,
        ]);
    }
    /**
     *
     * @param $args
     * @return string
     */
    protected function translate($args)
    {
        $translator = $this->getTranslator();
        return $translator->translate($args);
    }
}