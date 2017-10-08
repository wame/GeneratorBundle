<?php
declare(strict_types=1);

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestAppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [];

        if (in_array($this->getEnvironment(), ['test'])) {
            $bundles = [
                new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
                new Wame\GeneratorBundle\WameGeneratorBundle(),
            ];
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}