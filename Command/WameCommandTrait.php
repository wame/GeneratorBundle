<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait WameCommandTrait
{
    protected $rootDir;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        parent::initialize($input, $output);

        $entity = $input->getArgument('entity');
        if (strpos($entity, ':') === false  && $container->hasParameter('wame_generator.default_bundle')) {
            $input->setArgument('entity', $container->getParameter('wame_generator.default_bundle').':'.$entity);
        }
    }
}
