<?php
namespace ResourceTree\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use ResourceTree\View\Helper\TreeList;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TreeListFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        return new TreeList(
            $controllerPlugins->get('relatedItemsData')
            );
    }
}