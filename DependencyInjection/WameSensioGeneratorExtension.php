<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WameSensioGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['default_bundle'])) {
            $container->setParameter('wame_sensio_generator.default_bundle', $config['default_bundle']);
        }

        // CRUD
        $container->setParameter('wame_sensio_generator.crud.datatables', isset($config['crud']['datatables']));

        foreach ($config['class'] as $behaviour => $class) {
            $container->setParameter(sprintf('wame_sensio_generator.behaviour.%s.class', $behaviour), $class);
        }
    }
}
