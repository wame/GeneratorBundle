<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wame\SensioGeneratorBundle\Command\Helper\CrudQuestionHelper;
use Wame\SensioGeneratorBundle\MetaData\MetaEntityFactory;

trait WameCommandTrait
{
    protected $rootDir;

    protected $defaultBundle;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        parent::initialize($input, $output);

        if ($container->hasParameter('wame_sensio_generator.default_bundle')) {
            $this->defaultBundle = $container->getParameter('wame_sensio_generator.default_bundle');
        }

        if (!$input->hasArgument('entity') || !$input->getArgument('entity')) {
            return;
        }

        $entity = $input->getArgument('entity');

        if ($this->defaultBundle !== null && strpos($entity, ':') === false) {
            $input->setArgument('entity', $this->defaultBundle.':'.$entity);
        }
    }

    protected function validateEntityInput(InputInterface $input): void
    {
        list($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. Create it with the "doctrine:generate:entity" command and then execute this command again.', $entity, $bundle));
        }
    }

    protected function parseShortcutNotation(string $shortcut): array
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    protected function getEntityMetadata(string $entityClass): array
    {
        $factory = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        return $factory->getClassMetadata($entityClass)->getMetadata();
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

        $entity = WameValidators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        return MetaEntityFactory::createFromClassMetadata($metadata[0], $bundle);
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
