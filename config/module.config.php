<?php

namespace ResourceTree;

return [
    'api_adapters' => [
        'invokables' => [
            'not_related_items' => Api\Adapter\NotRelatedItemAdapter::class,
            'parent_items' => Api\Adapter\ParentItemAdapter::class,
            'child_items' => Api\Adapter\ChildItemAdapter::class,
            'item_trees' => Api\Adapter\ItemTreeAdapter::class,
            'json_item_trees' => Api\Adapter\JsonItemTreeAdapter::class,
            'root_items' => Api\Adapter\RootItemAdapter::class,
            'linked_items' => Api\Adapter\LinkedItemAdapter::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            OMEKA_PATH.'/modules/ResourceTree/view',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/ResourceTree/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [

        ],
        'factories' => [
            'ResourceTree\Controller\Admin\Index' => Service\Controller\Admin\IndexControllerFactory::class,
            'ResourceTree\Controller\Site\Index' => Service\Controller\Site\IndexControllerFactory::class,
        ],

    ],
    'controller_plugins' => [
        'factories' => [
            'relatedItemsData' => Service\ControllerPlugin\RelatedItemsDataFactory::class,
            'resourceTreeGenerator' => Service\ControllerPlugin\ResourceTreeGeneratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [

        ]
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'treeListNoLink' => View\Helper\TreeListNoLink::class,
        ],
        'factories' => [
            'resourceTree' => Service\ViewHelper\ResourceTreeFactory::class,
            'resourceTreeNoItem' => Service\ViewHelper\ResourceTreeNoItemFactory::class,
            'treeList' => Service\ViewHelper\TreeListFactory::class,
        ]
    ],
    'form_elements' => [
        'invokables' => [
//             Form\ConfigForm::class => Form\ConfigForm::class,
        ],
        'factories' => [
            Form\ConfigForm::class => Service\Form\ConfigFormFactory::class,
        ],
    ],
    'navigation_links' => [
        'invokables' => [
            'ResourceTree' => Site\Navigation\Link\ResourceTreeBrowse::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'resource-tree' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/resource-tree',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResourceTree\Controller\Site',
                                'controller' => 'index',
                                'action' => 'browse',
                            ],
                        ],
                    ],
                    'resource-tree-id' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/resource-tree/:id',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResourceTree\Controller\Site',
                                'controller' => 'index',
                                'action' => 'redirect',
                            ],
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '\d+',
                            ],
                        ],
                    ],
                    'get-resource-tree' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/get-resource-tree',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResourceTree\Controller\Site',
                                'controller' => 'index',
                                'action' => 'resource-tree',
                            ],
                        ],
                    ],
                ],
            ],
            'admin' => [
                'child_routes' => [
                    'resource-tree' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/resource-tree',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'resource-tree-link-form' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/resource-tree-link-form',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                        'controller' => 'Index',
                                        'action' => 'resource-tree-link-form',
                                    ],
                                ],
                            ],
                            'sidebar-select' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/sidebar-select',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                        'controller' => 'Index',
                                        'action' => 'sidebar-select',
                                    ],
                                ],
                            ],
                            'finish' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/finish',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                        'controller' => 'Index',
                                        'action' => 'finish',
                                    ],
                                ],
                            ],
                            'update-tree' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/update-tree',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                        'controller' => 'Index',
                                        'action' => 'update-tree',
                                    ],
                                ],
                            ],
                            'create-tree' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/create-tree',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'ResourceTree\Controller\Admin',
                                        'controller' => 'Index',
                                        'action' => 'create-tree',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Resource Tree',
                'route' => 'admin/resource-tree',
                'resource' => 'ResourceTree\Controller\Admin\Index',
                'pages' => [
                    [
                        'label'      => 'Update Tree Display', // @translate
                        'route'      => 'admin/resource-tree/update-tree',
                        'resource'   => 'ResourceTree\Controller\Admin\Index',
                    ],
                    [
                        'label'      => 'Create Tree By Linked Resource', // @translate
                        'route'      => 'admin/resource-tree/create-tree',
                        'resource'   => 'ResourceTree\Controller\Admin\Index',
                    ],
                ],
            ],
        ],
    ],
    'resourcetree' => [
        'settings' => [
            'resource_tree_folder_class' => '',
            'resource_tree_search_folder_class' => '',
            'resource_tree_document_class' => '',
            'resource_tree_validation_check' => true,
            'resource_tree_default_depth' => 1,
            'resource_tree_with_document_class' => false,
        ],
    ],
];
