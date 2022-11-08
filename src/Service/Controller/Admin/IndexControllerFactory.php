<?php
namespace ResourceTree\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use ResourceTree\Controller\Admin\IndexController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $controllerPlugins = $serviceLocator->get('ControllerPluginManager');
        return new IndexController(
            $controllerPlugins->get('resourceTreeGenerator'),
            $controllerPlugins->get('relatedItemsData')
            );
    }


}