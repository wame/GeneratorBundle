<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class WameGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../config'));
        $loader->load('services.yaml');

        if ($container->getParameter('kernel.environment') === 'test') {
            $config['default_bundle'] = 'WameGeneratorBundle';
        }
        $container->setParameter('wame_generator.default_bundle', $config['default_bundle'] ?? null);
        $container->setParameter('wame_generator.enable_traits', $config['enable_traits'] ?? true);
        $container->setParameter('wame_generator.enable_datatables', $config['enable_datatables'] ?? true);
        $container->setParameter('wame_generator.enable_voters', $config['enable_voters'] ?? true);

        // CRUD
        $container->setParameter('wame_generator.crud.datatables', isset($config['crud']['datatables']));

        foreach ($config['class'] as $behaviour => $class) {
            $container->setParameter(sprintf('wame_generator.behaviour.%s.class', $behaviour), $class);
        }
    }
}
