<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command;

use App\Entity\School;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wame\GeneratorBundle\Command\Helper\CrudQuestionHelper;
use Wame\GeneratorBundle\Inflector\Inflector;
use Wame\GeneratorBundle\MetaData\MetaEntityFactory;

trait WameCommandTrait
{
    protected $rootDir;

    protected $defaultBundle;
    protected $enableTraitOptions;
    protected $enableDatatables;
    protected $enableVoters;

    protected function initializeBaseSettings(InputInterface $input, OutputInterface $output)
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();
        parent::initialize($input, $output);

        $this->defaultBundle = $container->getParameter('wame_generator.default_bundle');
        $this->enableTraitOptions = $container->getParameter('wame_generator.enable_traits');
        $this->enableDatatables = $container->getParameter('wame_generator.enable_datatables');
        $this->enableVoters = $container->getParameter('wame_generator.enable_voters');

        if (!$input->hasArgument('entity') || !$input->getArgument('entity')) {
            return;
        }

        $entity = Inflector::classify($input->getArgument('entity'));

        if ($this->defaultBundle !== null && strpos($entity, ':') === false) {
            $input->setArgument('entity', $this->defaultBundle.':'.$entity);
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeBaseSettings($input, $output);
    }

    protected function validateEntityInput(InputInterface $input): void
    {
        list($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));

        try {
            $bundleNameSpace = $bundle ? $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) : 'App\\Entity';
            $entityClass = $bundleNameSpace.'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. Create it with the "doctrine:generate:entity" command and then execute this command again.', $entity, $bundle));
        }
    }

    protected function parseShortcutNotation(string $shortcut): array
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            return [null, $entity];
        }

        return [substr($entity, 0, $pos), substr($entity, $pos + 1)];
    }

    protected function getEntityMetadata(string $entityClass): ClassMetadata
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityMetadataItems = $em->getMetadataFactory()->getAllMetadata();
        foreach ($entityMetadataItems as $entityMetadata) {
            if ($entityMetadata->name === $entityClass) {
                return $entityMetadata;
            }
        }
        throw new EntityNotFoundException(sprintf('Could not find metaData for entity "%s"', $entityClass));
    }

    /**
     * Tries to make a path relative to the project, which prints nicer.
     */
    protected function makePathRelative(string $absolutePath): string
    {
        $projectRootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        return str_replace($projectRootDir.'/', '', realpath($absolutePath) ?: $absolutePath);
    }

    protected function getMetaEntityFormInput(InputInterface $input)
    {
        $entity = WameValidators::validateEntityName($input->getArgument('entity'));;
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = ($bundle ? $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) : 'App\\Entity').'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $bundle ? $this->getContainer()->get('kernel')->getBundle($bundle) : null;
        return MetaEntityFactory::createFromClassMetadata($metadata, $bundle);
    }

    protected function getCrudQuestionHelper(): CrudQuestionHelper
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || (new \ReflectionClass($question))->getName() !== (new \ReflectionClass(CrudQuestionHelper::class))->getName()) {
            $this->getHelperSet()->set($question = new CrudQuestionHelper(
                $this->getContainer()->get('doctrine'),
                $this->getContainer()->getParameter('kernel.bundles')
            ));
        }
        return $question;
    }
}
