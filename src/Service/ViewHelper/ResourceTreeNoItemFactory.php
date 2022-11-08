<?php
namespace ResourceTree\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use ResourceTree\View\Helper\ResourceTreeNoItem;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResourceTreeNoItemFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        return new ResourceTreeNoItem(
            $controllerPlugins->get('resourceTreeGenerator'),
            $controllerPlugins->get('relatedItemsData')
            );
    }
}