<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Wame\GeneratorBundle\Command\Helper\EntityQuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Wame\GeneratorBundle\DBAL\Types\Type;
use Wame\GeneratorBundle\Generator\WameEntityGenerator;
use Wame\GeneratorBundle\Inflector\Inflector;

/**
 * Wame version of GenerateDoctrineEntityCommand
 *
 * @author Kevin Driessen <kevin@wame.nl>
 */
class WameEntityCommand extends ContainerAwareCommand
{
    use WameCommandTrait;

    protected function configure()
    {
        $this->setName('wame:generate:entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to create with the new entity')
            ->addOption('no-validation', null, InputOption::VALUE_NONE, 'Do not ask to about adding field validation')
            ->addOption('no-blameable', null, InputOption::VALUE_NONE, 'Do not add `blameable` fields/behaviour on the new entity')
            ->addOption('no-timestampable', null, InputOption::VALUE_NONE, 'Do not add `timestampable` fields/behaviour on the new entity')
            ->addOption('no-softdeleteable', null, InputOption::VALUE_NONE, 'Do not soft-delete the new entity')
            ->addOption('behaviours', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Adds behavior (options are `blameable`,`timestampable`,`softdeleteable`)')
            ->addOption( 'savepoint', 's', InputOption::VALUE_NONE, 'Load savepoint (interactive mode only)')
            ->setHelp(<<<EOT
The <info>%command.name%</info> task generates a new Doctrine
entity inside a bundle:

<info>php %command.full_name% Post</info>

The above command would initialize a new entity in the following entity
namespace <info>AppBundle\Entity\Post</info>.

You can also optionally specify the fields you want to generate in the new
entity:

<info>php %command.full_name% Post --fields="title:string(255) body:text"</info>

To deactivate the interaction mode, simply use the <comment>--no-interaction</comment> (or <comment>--n</comment>) option
without forgetting to pass all needed options:

<info>php %command.full_name% Post --format=annotation --fields="
title:string(255 unique=true display=true) body:text(nullable=true) comment:one2many(targetEntity=Comment)
" --no-interaction --behaviours=timestampable --behaviours=blameable</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $questionHelper = $this->getQuestionHelper();

        $entity = WameValidators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $fields = $this->parseFields($input->getOption('fields'));
        WameValidators::validateFields($fields);
        $fields = WameValidators::normalizeFields($fields);

        $questionHelper->writeSection($output, 'Entity generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $behaviours = $input->hasOption('behaviours') ? $input->getOption('behaviours') : [];

        $this->getGenerator()->generate($bundle, $entity, $fields, $behaviours);

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

        if ($input->hasOption('savepoint') && $input->getOption('savepoint')) {
            $input = $this->loadSavePoint($input, $output);
        } else {
            $entityQuestionHelper->writeSection($output, 'Welcome to the WAME entity generator');

            if (!$input->hasArgument('entity') || !$input->getArgument('entity')) {
                $entityQuestionHelper->askEntityName($input, $output, $this->defaultBundle);
            }

            //Makes the entity-argument required
            $entity = WameValidators::validateEntityName($input->getArgument('entity'));
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

            if ($this->enableTraitOptions) {
                $entityQuestionHelper->askBehaviours($input, $output);
            }
        }

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $entityQuestionHelper));

        //questions for using __toString
        $entityQuestionHelper->askDisplayField($input, $output);
    }

    protected function parseFieldsAsJson(string $input): ?array
    {
        if (!$input) {
            return [];
        }

        //Remove newlines, tabs, space-indents
        $input = str_replace(["\r\n", "\n", "\t", '  '], '', $input);
        //Remove comma's at the end of an 'array'
        $input = preg_replace('/, ?}/i', '}', $input);

        //Remove spaces before and after json-characters (: , { } [ ])
        $input = preg_replace('/ +([:,}{\[\]])/i', '\1', $input);
        $input = preg_replace('/([:,}{\[\]]) +/i', '\1', $input);

        //Add quotes around keys => {key:value} becomes {"key":value}
        $input = preg_replace('/([,{])\'?([\w ]+)\'?:/i', '\1"\2":', $input);
        //Add quotes around values => {"key":some value} becomes {"key":"some value"}
        $input = preg_replace('/:\'?([^,}{\"]+)\'?([,}])/i', ':"\1"\2', $input);
        //Remove just added quotes from digits, null ands booleans => {"length":"255"} becomes {"length":255}
        $input = preg_replace('/:"(true|false|null|\d+)"/i', ':\1', $input);
        //Keys without value will become booleans => {nullable} will become {"nullable":true}
        $input = preg_replace('/([,{])([\w]+)([,}])/i', '\1"\2":true\3', $input);
        //Repeat same expressiong, because {nullable,unique} would become {"nullable":true,unique} if executed only once
        $input = preg_replace('/([,{])([\w]+)([,}])/i', '\1"\2":true\3', $input);

        $decodedInput = json_decode($input, true);

        if ($decodedInput === null) {
            throw new \InvalidArgumentException('The input could not be converted to valid json. Please make sure there aren\'t any forgotten/misplaced characters.');
        }

        foreach ($decodedInput as $fieldName => &$fieldData) {
            $fieldData['fieldName'] = Inflector::camelize($fieldName);
            $fieldData['columnName'] = Inflector::tableize($fieldName);
        }

        return $decodedInput;
    }

    /**
     * Copy of parseFields in Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand
     * with the exception of the first few lines that check if we should parse these fields as json
     * or if we are dealing with an array already.
     * @param string|array $input
     * @return array
     */
    protected function parseFields($input): array
    {
        if (is_array($input)) {
            return $input;
        }
        if ($input && strpos($input, '{') !== false) {
            return $this->parseFieldsAsJson($input);
        }

        $input = $input ?: '';

        $fields = array();
        foreach (preg_split('{(?:\([^\(]*\))(*SKIP)(*F)|\s+}', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            $fieldAttributes = array();
            if (strlen($name)) {
                $fieldAttributes['fieldName'] = $name;
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('{(.*)\((.*)\)}', $type, $matches);
                $fieldAttributes['type'] = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = null;
                if ('string' === $fieldAttributes['type']) {
                    $fieldAttributes['length'] = $length;
                }
                if (isset($matches[2][0]) && $length = $matches[2][0]) {
                    $attributesFound = array();
                    if (false !== strpos($length, '=')) {
                        preg_match_all('{([^,= ]+)=([^,= ]+)}', $length, $result);
                        $attributesFound = array_combine($result[1], $result[2]);
                    } else {
                        $fieldAttributes['length'] = $length;
                    }
                    $fieldAttributes = array_merge($fieldAttributes, $attributesFound);
                    foreach (array('length', 'precision', 'scale') as $intAttribute) {
                        if (isset($fieldAttributes[$intAttribute])) {
                            $fieldAttributes[$intAttribute] = (int) $fieldAttributes[$intAttribute];
                        }
                    }
                    foreach (array('nullable', 'unique') as $boolAttribute) {
                        if (isset($fieldAttributes[$boolAttribute])) {
                            $fieldAttributes[$boolAttribute] = filter_var($fieldAttributes[$boolAttribute], FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                }
                $fields[$name] = $fieldAttributes;
            }
        }
        return $fields;
    }

    protected function addFields(InputInterface $input, OutputInterface $output, EntityQuestionHelper $entityQuestionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            'You can add some fields now.',
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

            list ($bundle) = $this->parseShortcutNotation($input->getArgument('entity'));
            if ($type === Type::STRING) {
                $data['length'] = $entityQuestionHelper->askFieldLength($input, $output);
            } elseif ($type === Type::DECIMAL) {
                $data['precision'] = $entityQuestionHelper->askFieldPrecision($input, $output);
                $data['scale'] = $entityQuestionHelper->askFieldScale($input, $output);
            } elseif (Type::isRelationType($type)) {
                $data['targetEntity'] = $entityQuestionHelper->askTargetEntity($input, $output, $bundle, $columnName);
                $data['referencedColumnName'] = $entityQuestionHelper->askReferenceColumnName($input, $output, $data['targetEntity']);
            } elseif (Type::ENUM === $type) {
                $data['enumType'] = $entityQuestionHelper->askFieldEnumType($input, $output);
            } elseif (substr($type, -4) === 'Type') {
                $data['enumType'] = $type;
                $data['type'] = $type = Type::ENUM;
            }

            $data['nullable'] = $entityQuestionHelper->askFieldNullable($input, $output);
            $data['unique'] = $entityQuestionHelper->askFieldUnique($input, $output);

            $data['validation'] = $entityQuestionHelper->askFieldValidations($input, $output, $data);

            $fields[$columnName] = $data;

            $this->makeSavePoint($input, $fields);
        }
        return $fields;
    }

    protected function getGenerator(): WameEntityGenerator
    {
        return $this->getContainer()->get(WameEntityGenerator::class);
    }

    protected function makeSavePoint(InputInterface $input, $fields)
    {
        $input->setOption('fields', $fields);
        $cacheDir = $this->getContainer()->getParameter('kernel.cache_dir');
        $savePointPath = $cacheDir. '/generator-savepoint.txt';
        file_put_contents($savePointPath, serialize($input));
    }

    protected function loadSavePoint(InputInterface $input, OutputInterface $output): InputInterface
    {
        $entityQuestionHelper = $this->getQuestionHelper();
        $savePointPath = $this->getContainer()->getParameter('kernel.cache_dir'). '/generator-savepoint.txt';

        if (file_exists($savePointPath) === false) {
            throw new InvalidOptionException("No savepoint found. You cannot use the --savepoint option before a savepoint is created.");
        }

        /** @var InputInterface $savePointInput */
        $savePointInput = unserialize(file_get_contents($savePointPath));

        $input->setArgument('entity', $savePointInput->getArgument('entity'));
        $input->setOption('behaviours', $savePointInput->getOption('behaviours'));
        $input->setOption('fields', $savePointInput->getOption('fields'));

        $entityQuestionHelper->writeSection($output, 'Using entity savepoint for '. $input->getArgument('entity'));

        $fields = $this->parseFields($input->getOption('fields'));

        $output->writeln(sprintf('<info>Fields added so far: %s</info>', implode(', ', array_keys($fields))));

        return $input;
    }
}
