<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wame\SensioGeneratorBundle\Command;

use Wame\SensioGeneratorBundle\Command\Helper\EntityQuestionHelper;
use Wame\SensioGeneratorBundle\Generator\DoctrineEntityGenerator;
use Wame\SensioGeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Question\Question;
use Wame\SensioGeneratorBundle\Generator\WameEntityGenerator;
use Wame\SensioGeneratorBundle\Generator\WameRepositoryGenerator;
use Wame\SensioGeneratorBundle\Generator\WameTranslationGenerator;
use Wame\SensioGeneratorBundle\Inflector\Inflector;

/**
 * Initializes a Doctrine entity inside a bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WameEntityCommand extends \Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('test:generate:entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity class name to initialize (shortcut notation)')
            ->addOption('bundle', null, InputOption::VALUE_REQUIRED, 'Name of the Bundle in which the entity must be generated')            ->addOption('no-blameable', null, InputOption::VALUE_OPTIONAL, 'Do not add `blameable` fields/behaviour on the new entity')
            ->addOption('no-timestampable', null, InputOption::VALUE_OPTIONAL, 'Do not add `timestampable` fields/behaviour on the new entity')
            ->addOption('no-softdeleteable', null, InputOption::VALUE_OPTIONAL, 'Do not soft-delete the new entity')
            ->addOption('behaviours', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Adds behavior (options are `blameable`,`timestampable`,`softdeleteable`)')
            ->addOption('display-field', null, InputOption::VALUE_REQUIRED, 'The field that can represent the entity as a string')
            ->addOption('no-validation', null, InputOption::VALUE_NONE, 'Do not ask to about adding field validation')
        ;
        ;


        //TODO: remove option entity as this will be removed in 4.0
        //TODO: remove format option, since we'll always use annotation
        //TODO: make entity-argument required to save a lot of hassle.
    }

    /**
     * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle/MySampleBundle")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $fields = $this->parseFields($input->getOption('fields'));

        dump($fields);

        $questionHelper->writeSection($output, 'Entity generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $behaviours = $input->hasOption('behaviours') ? $input->getOption('behaviours') : [];

        /** @var WameEntityGenerator $generator */
        $generator = $this->getGenerator();
        $generatorResult = $generator->generateFromCommand($bundle, $entity, $fields, $behaviours);

        $output->writeln(sprintf('> Generating entity <info>%s</info>: <comment>OK!</comment>',$entity));
        $output->writeln(sprintf('> Generating repository class <info>%s</info>: <comment>OK!</comment>', $entity.'Repository'));

        $questionHelper->writeGeneratorSummary($output, []);
    }

    protected function isReservedWord(string $word): bool
    {
        return $this->getContainer()->get('doctrine')->getConnection()->getDatabasePlatform()->getReservedKeywordsList()->isKeyword($word);
    }

    protected function getQuestionHelper(): EntityQuestionHelper
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || (new \ReflectionClass($question))->getName() !== (new \ReflectionClass(EntityQuestionHelper::class))->getName()) {
            $this->getHelperSet()->set($question = new EntityQuestionHelper(
                $this->getContainer()->get('doctrine'),
                $this->getContainer()->getParameter('kernel.bundles'),
                $this->getContainer()->getParameter('doctrine.dbal.connection_factory.types')
            ));
        }
        return $question;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $entityQuestionHelper = $this->getQuestionHelper();
        $entityQuestionHelper->writeSection($output, 'Welcome to the WAME entity generator');

        //Makes the entity-argument required
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());
        if (in_array($bundle, $bundleNames, true) === false) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            return;
        }
        // check reserved words
        if ($this->isReservedWord($entity)) {
            $output->writeln(sprintf('<bg=red> "%s" is a reserved word</>.', $entity));
            return;
        }

        $entityQuestionHelper->askBehaviours($input, $output);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $entityQuestionHelper));

        //questions for using __toString
        $entityQuestionHelper->askDisplayField($input, $output);
    }

    protected function parseFields($inputFields)
    {
        //The parent method is private, so instead of parent::parseField, we use ReflectionClass
        $method = (new \ReflectionClass(parent::class))->getMethod('parseFields');
        $method->setAccessible(true);
        return $method->invoke($this, $inputFields);
    }


    protected function addFields(InputInterface $input, OutputInterface $output, EntityQuestionHelper $entityQuestionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ));

        while (true) {
            $output->writeln('');

            $columnName = $entityQuestionHelper->askFieldName($input, $output, $fields);
            if (!$columnName) {
                break;
            }

            $type = $entityQuestionHelper->askFieldType($input, $output, $columnName);

            $data = ['columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type];

            list ($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));
            if ($type == 'string') {
                $data['length'] = $entityQuestionHelper->askFieldLength($input, $output);
            } elseif ('decimal' === $type) {
                $data['precision'] = $entityQuestionHelper->askFieldPrecision($input, $output);
                $data['scale'] = $entityQuestionHelper->askFieldScale($input, $output);
            } elseif (in_array($type, ['one2one', 'many2one', 'many2many', 'one2many'], true)) {
                $data['targetEntity'] = $entityQuestionHelper->askTargetEntity($input, $output, $bundle);
                $data['referencedColumnName'] = $entityQuestionHelper->askReferenceColumnName($input, $output, $data['targetEntity']);
            } elseif ('enum' === $type) {
                list ($enumType, $enumTypeClass) = $entityQuestionHelper->askTargetEntity($input, $output, $bundle);
                $data['enumType'] = $enumType;
                $data['enumTypeClass'] = $enumTypeClass;
            }

            $data['nullable'] = $entityQuestionHelper->askFieldNullable($input, $output);

            if ($unique = $entityQuestionHelper->askFieldUnique($input, $output)) {
                $data['unique'] = $unique;
            }

            $data['validation'] = $entityQuestionHelper->askFieldValidations($input, $output);

            $fields[$columnName] = $data;
        }
        return $fields;
    }

    protected function createGenerator()
    {
        //Wame: Use different generator
        return new WameEntityGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get(WameTranslationGenerator::class),
            $this->getContainer()->get(WameRepositoryGenerator::class)
        );
    }
}
