<?php

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\Twig\InflectorExtension;

trait WameGeneratorTrait
{
    protected function getTwigEnvironment()
    {
        $this->setSkeletonDirs([__DIR__.'/../Resources/skeleton']);
        $twigEnvironment = parent::getTwigEnvironment();
        $twigEnvironment->addExtension(new InflectorExtension());
        return $twigEnvironment;
    }
}