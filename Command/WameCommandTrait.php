<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
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

    protected function validateEntityInput(InputInterface $input)
    {
        list($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. Create it with the "doctrine:generate:entity" command and then execute this command again.', $entity, $bundle));
        }
    }

    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    protected function getEntityMetadata($entity)
    {
        $factory = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        return $factory->getClassMetadata($entity)->getMetadata();
    }

    /**
     * Tries to make a path relative to the project, which prints nicer.
     *
     * @param string $absolutePath
     *
     * @return string
     */
    protected function makePathRelative($absolutePath)
    {
        $projectRootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        return str_replace($projectRootDir.'/', '', realpath($absolutePath) ?: $absolutePath);
    }
}
