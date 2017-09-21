<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command\Helper;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait HelperTrait
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @return \Doctrine\ORM\Mapping\ClassMetadata[]
     */
    protected function getExistingEntities(): array
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata[] $entityMetadata */
        $entityMetadata = $this->registry->getManager()->getMetadataFactory()->getAllMetadata();

        $entities = [];
        foreach ($entityMetadata as $meta) {
            $entityNamespace = $meta->getName();
            $shortName = $meta->reflClass->getShortName();
            $bundle = null;
            foreach ($this->bundles as $bundleName => $bundleNamespace) {
                if (strpos($entityNamespace, $bundleName) !== false) {
                    $bundle = $bundleName;
                }
            }
            $entities[$bundle . ':' . $shortName] = $meta;
        }

        return $entities;
    }

    protected function outputCompactOptionsList(OutputInterface $output, array $options, $offset = 0)
    {
        $count = $offset;
        $i = 0;
        foreach ($options as $option => $alias) {
            if ($count > static::MAX_OUTPUT_WIDTH) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen(($alias ? $alias . ': ' : '') . $option);
            if ($alias !== null) {
                $output->write(sprintf('<info>%s</info>: ', $alias));
            }
            $output->write(sprintf('<comment>%s</comment>', $option));
            if (count($options) !== $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
            $i++;
        }
        $output->writeln('');
    }
}
