<?php
namespace ResourceTree\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Omeka\Module\Manager;
use ResourceTree\Mvc\Controller\Plugin\ResourceTreeGenerator;
class ResourceTreeGeneratorFactory
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedNamed, array $options = null)
    {
        $api = $serviceLocator->get('Omeka\ApiManager');
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        $omekaModules = $serviceLocator->get('Omeka\ModuleManager');
        $targetProperty = '';
        $settings = $serviceLocator->get('Omeka\Settings');
        $module = $omekaModules->getModule('CleanUrl');
        if (!$module || Manager::STATE_ACTIVE != $module->getState()) {
            $targetProperty = '';
        } else {
            $propertyId = $settings->get('cleanurl_item')['property'];
            $api = $serviceLocator->get('Omeka\ApiManager');
            $response = $api->read('properties', ['id' => $propertyId]);
            $property = $response->getContent();
            $targetProperty = $property->vocabulary()->prefix() . ':' . $property->localName();
        }
        $plugin = new ResourceTreeGenerator(
            $api,
            $controllerPlugins->get('relatedItemsData'),
            $targetProperty,
            $serviceLocator->get('ViewHelperManager'),
            $serviceLocator->get('Omeka\Connection'),
            $settings
            );
        return $plugin;
    }
}

