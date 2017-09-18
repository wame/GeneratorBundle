<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\Twig\InflectorExtension;

trait WameGeneratorTrait
{
    protected $rootDir;

    protected function getTwigEnvironment()
    {
        if (is_dir($dir = $this->rootDir.'/Resources/WameSensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }
        $this->setSkeletonDirs([__DIR__.'/../Resources/skeleton']);
        $twigEnvironment = parent::getTwigEnvironment();
        $twigEnvironment->addExtension(new InflectorExtension());
        return $twigEnvironment;
    }
}
