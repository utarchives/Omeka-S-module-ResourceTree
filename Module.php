<?php

namespace ResourceTree;

use Omeka\Module\AbstractModule;
use Omeka\Module\Manager;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Exception;
use ResourceTree\Form\ConfigForm;

class Module extends AbstractModule
{

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');

        $acl->allow(
            null,
            ['ResourceTree\Controller\Site\Index',
                'ResourceTree\Api\Adapter\ParentItemAdapter',
                'ResourceTree\Entity\ParentItem',
                'ResourceTree\Api\Adapter\ChildItemAdapter',
                'ResourceTree\Entity\ChildItem',
                'ResourceTree\Api\Adapter\JsonItemTreeAdapter',
                'ResourceTree\Entity\JsonItemTree',
            ]
            );


    }
    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $sql = <<<'SQL'
CREATE TABLE item_tree(
  id INT AUTO_INCREMENT NOT NULL
  , parent_item_id INT NOT NULL
  , child_item_id INT NOT NULL
  , depth INT NOT NULL
  , is_here TINYINT(1) NOT NULL
  , is_parent  TINYINT(1) NOT NULL
  , INDEX IDX_7F0E1A2760272618(parent_item_id)
  , INDEX IDX_7F0E1A2770DB1A45(child_item_id)
  , PRIMARY KEY (id)
) DEFAULT CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

SQL;
        $connection->exec($sql);
        $sql = <<<'SQL'
create view not_related_item as
SELECT
  b.id
  , owner_id
  , resource_class_id
  , resource_template_id
  , is_public
  , created
  , modified
  , resource_type
FROM
  resource a
  inner join item b
    on a.id = b.id
  left join item_tree c
    on b.id = c.child_item_id
    and c.is_here
where
  c.id is null;
SQL;
        $connection->exec($sql);
        $sql = <<<'SQL'
create view parent_item as
select
  b.id
  , b.id as item_id
  , c.id as sort
  , c.is_here
  , c.depth
  , c.child_item_id
  , b.resource_class_id
  , b.resource_class_id as target_resource_class_id
  , e.value as title
  , b.resource_template_id
  , b.is_public
from
  item a
  inner join resource b
    on a.id = b.id
  inner join item_tree c
    on a.id = c.parent_item_id
  inner join value e
    on a.id = e.resource_id
  inner join property f
    on e.property_id = f.id
    and f.local_name = 'title'
  inner join vocabulary g
    on f.vocabulary_id = g.id
    and g.prefix = 'dcterms' ;
SQL;
        $connection->exec($sql);
        $sql = <<<'SQL'
create view child_item as
select
  b.id
  , b.id as item_id
  , c.id as sort
  , c.is_here
  , c.depth
  , c.parent_item_id
  , b.resource_class_id
  , b.resource_class_id as target_resource_class_id
  , e.value as title
  , b.resource_template_id
  , b.is_public
from
  item a
  inner join resource b
    on a.id = b.id
  inner join item_tree c
    on a.id = c.child_item_id
    and depth != 1
  inner join value e
    on a.id = e.resource_id
  inner join property f
    on e.property_id = f.id
    and f.local_name = 'title'
  inner join vocabulary g
    on f.vocabulary_id = g.id
    and g.prefix = 'dcterms' ;
SQL;
        $connection->exec($sql);
        $sql = <<<'SQL'
create view root_item as
select a.id, o.resource_class_id
from
item a
inner join
resource o
on
a.id = o.id
inner join
value b
on
a.id = b.value_resource_id
left join
(select resource_id
from
value
where
value_resource_id is not null
and type in ('resource', 'resource:item')) c
on
a.id = c.resource_id
where
c.resource_id is null;
SQL;
        $connection->exec($sql);
        $sql = <<<'SQL'
create view linked_item as
select
  a.id
  , b.value_resource_id as parent_item_id
  , o.resource_class_id
  , p.resource_class_id AS parent_resource_class_id
from
  item a
  inner join resource o
    on a.id = o.id
  inner join value b
    on a.id = b.resource_id
    and b.type in ('resource', 'resource:item')
	inner join resource p
	on b.value_resource_id = p.id;
SQL;
        $connection->exec($sql);
        $this->manageSiteSettings($serviceLocator, 'install');
    }
    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $omekaModules = $serviceLocator->get('Omeka\ModuleManager');
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $messenger = $controllerPlugins->get('messenger');
        $module = $omekaModules->getModule('SpecialCharacterSearch');
        if (Manager::STATE_ACTIVE == $module->getState() || Manager::STATE_NOT_ACTIVE == $module->getState()) {
            throw new Exception('Should uninstall SpecialCharacterSearch');
        }
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec('DROP VIEW IF EXISTS not_related_item;');
        $connection->exec('DROP VIEW IF EXISTS parent_item;');
        $connection->exec('DROP VIEW IF EXISTS child_item;');
        $connection->exec('DROP VIEW IF EXISTS root_item;');
        $connection->exec('DROP VIEW IF EXISTS linked_item;');
        $connection->exec('DROP TABLE IF EXISTS item_tree;');
        $this->manageSettings($serviceLocator->get('Omeka\Settings'), 'uninstall');
        $this->manageSiteSettings($serviceLocator, 'install');
    }
    /**
     *
     * @param $settings
     * @param $process
     * @param string $key
     */
    protected function manageSettings($settings, $process, $key = 'settings')
    {
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)][$key];
        foreach ($defaultSettings as $name => $value) {
            switch ($process) {
                case 'install':
                    $settings->set($name, $value);
                    break;
                case 'uninstall':
                    $settings->delete($name);
                    break;
            }
        }
    }
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $process
     */
    protected function manageSiteSettings(ServiceLocatorInterface $serviceLocator, $process)
    {
        $siteSettings = $serviceLocator->get('Omeka\Settings\Site');
        $api = $serviceLocator->get('Omeka\ApiManager');
        $sites = $api->search('sites')->getContent();
        foreach ($sites as $site) {
            $siteSettings->setTargetId($site->id());
            $this->manageSettings($siteSettings, $process, 'site_settings');
        }
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $formElementManager = $services->get('FormElementManager');

        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $data['resource_tree_config'][$name] = $settings->get($name);
        }
        $renderer->ckEditor();
        $form = $formElementManager->get(ConfigForm::class);
        $form->init();
        $form->setData($data);
        $html = $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        $form = $this->getServiceLocator()->get('FormElementManager')
        ->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($params as $name => $value) {
            if (isset($defaultSettings[$name])) {
                $settings->set($name, $value);
            }
        }
    }
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
//         $sharedEventManager->attach(
//             \Omeka\Api\Adapter\ItemAdapter::class,
//             'api.hydrate.pre',
//             [$this, 'handleItemTree']
//             );
        //         $sharedEventManager->attach(
        //             \Omeka\Api\Adapter\ItemAdapter::class,
        //             'api.create.pre',
        //             [$this, 'createTree']
        //             );
//         $sharedEventManager->attach(
//             \Omeka\Api\Adapter\ItemAdapter::class,
//             'api.update.post',
//             [$this, 'recreateTree']
//             );
//         $sharedEventManager->attach(
//             \Omeka\Api\Adapter\ItemAdapter::class,
//             'api.delete.post',
//             [$this, 'deleteTree']
//             );
        //         $sharedEventManager->attach(
        //             'Omeka\Controller\Site\Item',
        //             'view.show.before',
        //             function (Event $event) {
        //                 echo $event->getTarget()->partial('common/test.phtml');
        //             }
        //             );
    }
    /**
     * validation for resource tree
     * @param Event $event
     */
    public function handleItemTree(Event $event)
    {
        $serviceLocator = $this->getServiceLocator();
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $relatedItemsData = $controllerPlugins->get('relatedItemsData');
        $settings = $serviceLocator->get('Omeka\Settings');
        $treeValidation = $settings->get('resource_tree_validation_check', true);
        if ($treeValidation) {
            $request = $event->getParam('request');
            $errorStore = $event->getParam('errorStore');
            $parentCount = 0;
            $resourceClassId = $request->getContent()['o:resource_class']['o:id'];
            if (empty($resourceClassId)) {
                return;
            }
            if (!strcmp($resourceClassId, strval($relatedItemsData->getFolderResourceClassId())) == 0
                && !strcmp($resourceClassId, strval($relatedItemsData->getSearchFolderResourceClassId())) == 0
                && !strcmp($resourceClassId, strval($relatedItemsData->getDocumentResourceClassId())) == 0) {
                    return;
                }
                foreach ($request->getContent() as $key => $values) {
                    $multiParents = false;
                    if (!is_array($values)) {
                        continue;
                    }
                    foreach($values as $value) {
                        if (!is_array($value)) {
                            continue;
                        }
                        if (!array_key_exists('type', $value)) {
                            continue;
                        }
                        if (strcmp($value['type'], 'resource') != 0 && strcmp($value['type'], 'resource:item') != 0) {
                            continue;
                        }
                        if (!array_key_exists('value_resource_id', $value)) {
                            continue;
                        }
                        if (empty($value['value_resource_id'])) {
                            continue;
                        }
                        if (strcmp($request->getId(), $value['value_resource_id']) == 0) {
                            $errorStore->addError($key, 'Related item should be diffrent item.'); // @translate
                            return;
                        }
                        $parentResourceClassId = $relatedItemsData
                        ->getItemResourceClassId($value['value_resource_id']);
                        if ($parentResourceClassId == -1) {
                            continue;
                        }
                        // if parent item is not folder or no search folder check parents
                        if (strcmp(strval($parentResourceClassId), strval($relatedItemsData->getFolderResourceClassId())) != 0 &&
                            strcmp(strval($parentResourceClassId), strval($relatedItemsData->getSearchFolderResourceClassId())) != 0) {
                            $api = $serviceLocator->get('Omeka\ApiManager');
                            $response = $api->search('item_trees', ['child_item_id' => $value['value_resource_id']]);
                            if ($response->getTotalResults() == 0) {
                                $errorStore->addError($key, 'Related item should have parent'); // @translate
                                return;
                            }

                        }
                        $result = $relatedItemsData->validateTree($parentResourceClassId, $resourceClassId, false);
                        // if not correct parent item
                        if (!$result) {
                            $errorStore->addError($key, 'Related item is not correct'); // @translate
                            return;
                        }
                        $parentCount++;
                        if ($parentCount > 1) {
                            $multiParents = true;
                            break;
                        }
                    }
                    if ($multiParents) {
                        $errorStore->addError($key, 'Item can has only one relation'); // @translate
                        return;
                    }
                }
        }
    }
    /**
     * delete item tree
     * @param Event $event
     */
    public function deleteTree(Event $event) {
        $serviceLocator = $this->getServiceLocator();
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $relatedItemsData = $controllerPlugins->get('relatedItemsData');
        $settings = $serviceLocator->get('Omeka\Settings');
        $treeValidation = $settings->get('resource_tree_validation_check', true);
        $request = $event->getParam('request');
        $relatedItemsData->deleteTree($request->getId());
    }
    /**
     * recreate item tree
     * @param Event $event
     */
    public function recreateTree(Event $event) {
        $serviceLocator = $this->getServiceLocator();
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $relatedItemsData = $controllerPlugins->get('relatedItemsData');
        $resourceTree = $controllerPlugins->get('resourceTreeGenerator');
        $settings = $serviceLocator->get('Omeka\Settings');
        $treeValidation = $settings->get('resource_tree_validation_check', true);
        $request = $event->getParam('request');
        $relatedItemsData->createTree($request);
        $resourceTree->createJsonItemTree();
    }

    /**
     * Display the quick Resource Tree.
     *
     * @param Event $event
     */
    public function displayResourceTree(Event $event)
    {
        $view = $event->getTarget();
        $resource = $event->getTarget()->resource;
        echo $view->resourceTree($resource->id());
    }
    /**
     * Display the quick Resource Tree.
     *
     * @param Event $event
     */
    public function displayResourceTreeNoItem(Event $event)
    {
        $view = $event->getTarget();
        echo $view->resourceTreeNoItem();
    }
}
