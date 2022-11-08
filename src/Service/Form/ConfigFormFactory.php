<?php
namespace ResourceTree\Service\Form;

use ResourceTree\Form\ConfigForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $translator = $serviceLocator->get('MvcTranslator');

        $form = new ConfigForm(null, $options);
        $form->setTranslator($translator);
        return $form;
    }
}
