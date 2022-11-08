<?php
namespace ResourceTree\Service\ViewHelper;

use Omeka\Module\Manager;
use ResourceTree\View\Helper\ResourceTree;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResourceTreeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $omekaModules = $serviceLocator->get('Omeka\ModuleManager');
        $targetProperty = '';
        $settings = $serviceLocator->get('Omeka\Settings');
        $module = $omekaModules->getModule('CleanUrl');
        if (!$module || Manager::STATE_ACTIVE != $module->getState()) {
            $targetProperty = '';
            $targetController = '';
        } else {
            $propertyId = $settings->get('cleanurl_item')['property'];
            $api = $serviceLocator->get('Omeka\ApiManager');
            $response = $api->read('properties', ['id' => $propertyId]);
            $property = $response->getContent();
            $targetProperty = $property->vocabulary()->prefix() . ':' . $property->localName();
            $targetController = $settings->get('cleanurl_item_generic');
        }
        return new ResourceTree(
            $controllerPlugins->get('resourceTreeGenerator'),
            $controllerPlugins->get('relatedItemsData'),
            $targetProperty,
            $targetController,
            $settings
            );
    }
}