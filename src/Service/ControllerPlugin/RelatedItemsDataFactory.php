<?php
namespace ResourceTree\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ResourceTree\Mvc\Controller\Plugin\RelatedItemsData;

class RelatedItemsDataFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedNamed, array $options = null)
    {
        $api = $serviceLocator->get('Omeka\ApiManager');
        $settings = $serviceLocator->get('Omeka\Settings');
        $plugin = new RelatedItemsData(
            $api,
            $settings,
            $serviceLocator->get('Omeka\Connection')
        );
        return $plugin;
    }
}
