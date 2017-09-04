<?php
declare(strict_types=1);

/*
 * Instead of extending the 'GenerateDoctrineEntityCommand', this base class is used
 * to add functionality which can be called in the GenerateDoctrineEntityCommand.
 */

namespace Wame\SensioGeneratorBundle\Command;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraint;
use Wame\SensioGeneratorBundle\Command\Helper\QuestionHelper;
use Wame\SensioGeneratorBundle\Util\ClassFunctions;

abstract class GenerateWameEntityCommand extends GenerateDoctrineCommand
{
    protected $behaviours = [
        'timestampable' => true,
        'blameable' => true,
        'softdeleteable' => true,
    ];

    protected $constraints;

    protected $validationFieldTypes = [
        Type::BOOLEAN,
        Type::INTEGER,
        Type::SMALLINT,
        Type::BIGINT,
        Type::STRING,
        Type::TEXT,
        Type::DATETIME,
        Type::DATETIMETZ,
        Type::DATE,
        Type::TIME,
        Type::DECIMAL,
        Type::FLOAT,
        Type::BLOB,
    ];

    /**
     * @var \ReflectionClass
     */
    protected static $staticConstraintReflection = [];

    protected $ignoredConstraintOptions = [
        'payload',
    ];

    protected function configure()
    {
        $this
            ->addOption('no-blameable', null, InputOption::VALUE_OPTIONAL, 'Do not add `blameable` fields/behaviour on the new entity')
            ->addOption('no-timestampable', null, InputOption::VALUE_OPTIONAL, 'Do not add `timestampable` fields/behaviour on the new entity')
            ->addOption('no-softdeleteable', null, InputOption::VALUE_OPTIONAL, 'Do not soft-delete the new entity')
            ->addOption('behaviours', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Internal option; use --no-* options instead')
            ->addOption('display-field', null, InputOption::VALUE_REQUIRED, 'The field that can represent the entity as a string')
            ->addOption('no-validation', null, InputOption::VALUE_NONE, 'Do not ask to about adding field validation')
        ;
    }

    protected function parseFields($fields)
    {
        //NOTE: we assume this method is called AFTER parseField in 'GenerateDoctrineEntityCommand'
        foreach ($fields as &$field) {
            $validationOption = $field['validation'] ?? null;
            if ($validationOption && !is_array($validationOption)) {
                $validations = explode(';', $validationOption);
                $newValidationOption = [];
                foreach ($validations as $validation) {
                    $newValidationOption[] = [
                        'type' => $validation,
                        'options' => [],    //TODO: it might be nice if we can pass options as well
                    ];
                }
                $field['validation'] = $newValidationOption;
            }
        }
        return $fields;
    }

    /**
     * @param Constraint[] $constraints
     * @return array
     */
    protected function getConstraintOptions($constraints)
    {
        $constraintClasses = $this->getConstraintClasses($constraints);
        $constraintOptions = !empty($constraints)
            ? array_combine(range(1, count($constraints)), $constraintClasses)
            : [];

        return $constraintOptions;
    }

    /**
     * @param Constraint[] $constraints
     * @return array
     */
    protected function getConstraintClasses($constraints)
    {
        $constraintClasses = [];
        foreach ($constraints as $constraintClass => $constraint) {
            $name = substr($constraintClass, strrpos($constraintClass, '\\') + 1);
            $constraintClasses[$constraintClass] = $name;
        }
        return $constraintClasses;
    }

    protected function addBehaviorInteraction(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        // ask about timestampable/blameable/softdeletable behaviours
        $behavioursStr = '<comment>'.implode(', ', array_keys($this->behaviours)).'</comment>';
        $allBehaviours = (!$input->getOption('no-blameable') && !$input->getOption('no-timestampable') && !$input->getOption('no-softdeleteable'));
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Add default behaviours ('.$behavioursStr.')', $allBehaviours ? 'yes' : 'no'), $allBehaviours);
        if ($questionHelper->ask($input, $output, $question)) {
            $behaviours = array_keys($this->behaviours);
        } else {
            $behaviours = [];
            foreach ($this->behaviours as $behaviour => $enabled) {
                $enabled = $input->getOption('no-'.$behaviour) ? false : $enabled;
                $question = new ConfirmationQuestion($questionHelper->getQuestion(sprintf('Add <comment>%s</comment> behaviour', $behaviour), $enabled ? 'yes' : 'no'), $enabled);
                if ($questionHelper->ask($input, $output, $question)) {
                    $behaviours[] = $behaviour;
                }
            }
        }

        $input->setOption('behaviours', $behaviours);
    }

    protected function addDisplayFieldInteraction(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $entityFields = $input->getOption('fields');

        $output->writeln([
            '',
            'If possible we should add a __toString method, you can do this by picking a "display field"',
            ''
        ]);

        $displayFieldOptions = array_filter(array_map(function ($field) {
            if (!in_array($field['type'], ['string', 'text', ''], true)) {
                return false;
            }
            return $field['fieldName'];
        }, $entityFields));

        $displayFieldOptions = $displayFieldOptions
            ? array_combine(range(1, count($displayFieldOptions)), $displayFieldOptions)
            : [];

        $output->write('<info>Available fields:</info> ');
        $this->outputCompactOptionsList($output, array_flip($displayFieldOptions), 20);

        $defaultField = null;
        if (isset($entityFields['title'])) {
            $defaultField = 'title';
        } elseif (isset($entityFields['name'])) {
            $defaultField = 'name';
        }

        $question = new Question($questionHelper->getQuestion('Which field you want to use? (leave empty to skip)', $defaultField), $defaultField);
        $question->setAutocompleterValues(array_keys($displayFieldOptions));
        $question->setValidator(function ($field) use ($displayFieldOptions) {
            if (!$field) {
                return null;
            }
            if (ctype_digit($field) && isset($displayFieldOptions[$field])) {
                return $displayFieldOptions[$field];
            }
            if (!in_array($field, $displayFieldOptions)) {
                throw new \InvalidArgumentException(sprintf('Invalid field "%s".', $field));
            }

            return $field;
        });

        $displayField = $questionHelper->ask($input, $output, $question);
        $input->setOption('display-field', $displayField);
    }

    protected function guessMoreFieldTypes($columnName, $defaultType)
    {
        if (substr($columnName, -3) === '_on') {
            return 'datetime';
        } if (substr($columnName, -4) === 'date') {
            return 'date';
        } if (substr($columnName, -3) === '_id') {
            return 'many2one';
        } if (substr($columnName, -5) === 'count') {
            return 'integer';
        } if (in_array($columnName, ['summary', 'description', 'text'])) {
            return 'text';
        } if (substr($columnName, -5) === 'price') {
            return 'double';
        }
        return $defaultType;
    }

    protected function addInteractionForRelationTypes(InputInterface $input, OutputInterface $output, &$data, $type)
    {
        if (!in_array($type, ['one2one', 'many2one', 'many2many', 'one2many'], true)) {
            return;
        }
        $columnName = $data['columnName'];
        $questionHelper = $this->getQuestionHelper();
        list($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));
        $existingEntities = $this->getExistingEntities();
        $existingEntityOptions = !empty($existingEntities)
            ? array_combine(range(1, count($existingEntities)), array_keys($existingEntities))
            : [];

        if (substr($columnName, -3) === '_id') {
            $data['fieldName'] = lcfirst(Container::camelize(substr($columnName, 0, -3)));
        }

        // In Doctrine joinColumns are nullable by default;
        $data['nullable'] = true;

        // guess target entity based on fieldName (and type)
        $targetEntitySuggestion = null; //TODO: wamegenerator heeft hier een functie voor, maar die lijkt stuk te zijn

        $output->write('<info>Available Entities:</info> ');
        $this->outputCompactOptionsList($output, array_flip($existingEntityOptions));
        $output->writeln('');

        $question = new Question($questionHelper->getQuestion('Related Entity', $targetEntitySuggestion), $targetEntitySuggestion);
        $question->setAutocompleterValues(array_keys($existingEntities));
        $question->setNormalizer($this->getEntityNormalizer($bundle, $existingEntityOptions));
        // TODO: should we add a validator that checks if the entity exists?
        $data['targetEntity'] = $questionHelper->ask($input, $output, $question);

        $refColumn = 'id';
        if (isset($existingEntities[$data['targetEntity']])) {
            $targetEntityMeta = $existingEntities[$data['targetEntity']];
            try {
                $refColumn = $targetEntityMeta->getSingleIdentifierColumnName();
            } catch (\Exception $e) {
                // do nothing
            }
        }

        $question = new Question($questionHelper->getQuestion('Reference column for related Entity', $refColumn), $refColumn);
        $data['referencedColumnName'] = $questionHelper->ask($input, $output, $question);
    }

    protected function addInteractionForEnumTypes(InputInterface $input, OutputInterface $output, &$data, $type)
    {
        if ($type !== 'enum') {
            return;
        }
        $questionHelper = $this->getQuestionHelper();
        list($bundle, $entity) = $this->parseShortcutNotation($input->getArgument('entity'));
        $enumTypes = $this->getEnumTypes();
        $enumOptionsList = [ 'Create a new Enum Type' => 0 ];
        if (count($enumTypes) > 0) {
            $enumOptionsList = array_merge(
                $enumOptionsList,
                array_combine(array_keys($enumTypes), range(1, count($enumTypes)))
            );
        }
        $this->outputCompactOptionsList($output, $enumOptionsList);

        $question = new Question($questionHelper->getQuestion('Which enum type', ''), '');
        $question->setAutocompleterValues(array_keys($enumTypes));
        $question->setValidator(function ($type) use ($enumOptionsList) {
            if (!$type) {
                return null;
            }
            if (is_int($type) || ctype_digit($type)) {
                if (!in_array($type, $enumOptionsList)) {
                    throw new \InvalidArgumentException(sprintf("%d is not a valid option", $type));
                }
                if ($type == 0) {
                    return null;
                }
                return array_search($type, $enumOptionsList);
            }
            if (!array_key_exists($type, $enumOptionsList)) {
                throw new \InvalidArgumentException(sprintf("'%s' is not a valid option", $type));
            }
            return $type;
        });

        $enumType = $questionHelper->ask($input, $output, $question);

        if (!$enumType) {
            $output->writeln([
                '',
                'You choose to create a new Enum Type, we\'ll fire up the Enum Generator now...',
                '',
            ]);
            $enumGeneratorCommand = $this->getApplication()->get('wame:generate:enum');
            $enumGeneratorInput = new ArrayInput([
                'name' => $entity . ucfirst($data['fieldName']) . 'Type',
                '--bundle' => $bundle,
            ], $enumGeneratorCommand->getDefinition());
            $returnCode = $enumGeneratorCommand->run($enumGeneratorInput, $output);
            if ($returnCode == 0) {
                $enumType = $enumGeneratorInput->getArgument('name');
                $enumClass = $this->getBundleNamespace($bundle) . '\\DBAL\\Types\\' . $enumType;
                $data['enumType'] = $enumType;
                $data['enumTypeClass'] = $enumClass;
                $enumTypes[$enumType] = $enumClass;
            }
            // TODO: output warning if this point is reached?
        } else {
            $data['enumType'] = $enumType;
            $data['enumTypeClass'] = $enumTypes[$enumType];
        }
    }

    protected function addInteractionForValidations(InputInterface $input, OutputInterface $output, &$data)
    {
        $questionHelper = $this->getQuestionHelper();
        if (!$input->hasOption('no-validation') || !$input->getOption('no-validation')) {
            $askValidation = in_array($data['type'], $this->validationFieldTypes);
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Add validation to this field', $askValidation ? 'yes' : 'no'), $askValidation);
            $askValidation = $questionHelper->ask($input, $output, $question);
            if ($askValidation) {
                $data['validation'] = $this->askFieldValidation($input, $output, $questionHelper, $data);
            }
        }
    }

    protected function askFieldValidation(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, array $fieldData)
    {
        $first = true;
        $fieldConstraints = [];

        $questionHelper->writeSection($output, sprintf("Property validation for '%s'", $fieldData['fieldName']));

        $output->writeln([
            'It\'s a good idea to add validation to your entity properties!',
            'Check the documentation for more into:',
            'http://symfony.com/doc/current/book/validation.html',
            '',
            'These are the default available constraints:'
        ]);

        $constraints = $this->getPropertyValidationConstraints();
        $constraintClasses = [];
        foreach ($constraints as $constraintClass => $constraint) {
            $name = substr($constraintClass, strrpos($constraintClass, '\\') + 1);
            $constraintClasses[$constraintClass] = $name;
        }
        $constraintClassMapping = array_flip($constraintClasses);
        $constraintOptions = !empty($constraints)
            ? array_combine(range(1, count($constraints)), $constraintClasses)
            : [];

        $this->outputCompactOptionsList($output, array_flip($constraintOptions));

        $constraintValidator = function ($constraint) use ($constraintOptions) {
            if (!$constraint) {
                return null;
            }
            if (ctype_digit($constraint) && isset($constraintOptions[$constraint])) {
                $constraint = $constraintOptions[$constraint];
            } elseif (!in_array($constraint, $constraintOptions)) {
                throw new \InvalidArgumentException(sprintf(
                    "Unknown validation constraint '%s'! Available options: [%s]",
                    $constraint,
                    implode(', ', $constraintOptions)
                ));
            }
            return $constraint;
        };

        $constraintOptionNormalizer = function ($value) {
            if (is_int($value) || ctype_digit($value)) {
                return (int) $value;
            }
            if (is_array($value)) {
                return $value;
            }
            if ($value === 'yes') {
                return true;
            }
            if ($value === 'no') {
                return false;
            }
            try {
                $decodeValue = @json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Could not decode value as json');
                }
                return $decodeValue;
            } catch (\Exception $e) {
                return $value;
            }
        };

        while (true) {
            $output->writeln('');
            if ($first) {
                $question = new Question($questionHelper->getQuestion('Which validation constraint should we add', null, '?'));
                $first = false;
            } else {
                $question = new Question($questionHelper->getQuestion('Also add this validation (press <return> to stop adding)', null, ':'));
            }
            $question->setValidator($constraintValidator);
            $type = $questionHelper->ask($input, $output, $question);

            if (!$type) {
                // TODO?: ask if user is sure
                break;
            }

            $fieldConstraint = [
                'type' => $type,
                'options' => [],
            ];

            $constraintClass = $constraintClassMapping[$type];
            $constraint = $constraints[$constraintClass];
            $constraintRef = $this->getValidationConstraintReflection($constraintClass);

//            $constraintRef->getDefaultProperties();
            $output->writeln([
                '',
                sprintf('You now have the option to set some options for the %s constraint', $type),
                '',
                'To enter an array or other \'complex\' structure as value',
                'please use valid json. e.g <info>["foo", "bar"]</info>',
                ''
            ]);

            // Make sure we set all required options
            foreach ($constraint->getRequiredOptions() as $option) {
                if (array_key_exists($option, $fieldConstraint['options'])) {
                    continue;
                }
                $value = null;
                if ($constraintRef->hasProperty($option)) {
                    $value = $constraintRef->getProperty($option)->getValue($constraint);
                }
                $question = new Question($questionHelper->getQuestion(sprintf('Value for the required <comment>%s</comment> option', $option), $value), $value);
                $question->setAutocompleterValues($constraintOptions);
                $question->setNormalizer($constraintOptionNormalizer);
                $value = $questionHelper->ask($input, $output, $question);
                $fieldConstraint['options'][$option] = $value;
                $output->writeln('');
            }

            // Ask about other public properties, as possible option
            foreach ($constraintRef->getDefaultProperties() as $property => $defaultValue) {
                if (array_key_exists($property, $fieldConstraint['options'])) {
                    continue;
                }
                if (in_array($property, $this->ignoredConstraintOptions)) {
                    continue;
                }
                $propertyRef = $constraintRef->getProperty($property);
                if (!$propertyRef->isPublic() || $propertyRef->isStatic()) {
                    continue;
                }

                if (is_array($defaultValue)) {
                    $defaultValueDisplay = json_encode($defaultValue, (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) & ~JSON_PRETTY_PRINT);
                } else {
                    $defaultValueDisplay = $defaultValue;
                }

                $question = new Question($questionHelper->getQuestion(sprintf('Value for the <comment>%s</comment> option', $property), $defaultValueDisplay), $defaultValue);
                $question->setAutocompleterValues($constraintOptions);
                $question->setNormalizer($constraintOptionNormalizer);
                $value = $questionHelper->ask($input, $output, $question);

                if ($defaultValue !== $value) {
                    // Don't add options that already have the default value (e.g. for messages and such)
                    $fieldConstraint['options'][$property] = $value;
                }
                $output->writeln('');
            }

            $fieldConstraints[] = $fieldConstraint;
        }

        return $fieldConstraints;
    }

    /**
     * @param $constraint
     * @return \ReflectionClass
     */
    protected function getValidationConstraintReflection($constraint)
    {
        if (isset(static::$staticConstraintReflection[$constraint])) {
            return static::$staticConstraintReflection[$constraint];
        }
        if (!isset($this->constraints[$constraint])) {
            throw new \InvalidArgumentException(sprintf('Unknown constraint "%s"', $constraint));
        }

        static::$staticConstraintReflection[$constraint] = new \ReflectionClass($this->constraints[$constraint]);

        return static::$staticConstraintReflection[$constraint];
    }

    /**
     * @return array|\Symfony\Component\Validator\Constraint[]
     */
    protected function getPropertyValidationConstraints()
    {
        if ($this->constraints) {
            return $this->constraints;
        }

        $constraints = [];
        $componentNamespace = '\Symfony\Component\Validator\Constraints';

        // Try to get all default constraints by scanning the Component's Constraints folder
        $dir = dirname((new \ReflectionClass($componentNamespace.'\Valid'))->getFileName());
        foreach (scandir($dir, null) as $file) {
            if (preg_match('/^(.+)(?!(Validator|Provider))\.php$/', $file, $matches)) {
                $constraintClass = $componentNamespace . '\\' . $matches[1];
                if (!is_subclass_of($constraintClass, '\Symfony\Component\Validator\Constraint')) {
                    continue;
                }
                $ref = new \ReflectionClass($constraintClass);
                if ($ref->isAbstract()) {
                    continue;
                }

                /** @var \Symfony\Component\Validator\Constraint $constraint */
                try {
                    $constraint = new $constraintClass();
                } catch (\Exception $e) {
                    continue;
                }
                if (!in_array(\Symfony\Component\Validator\Constraint::PROPERTY_CONSTRAINT, (array)$constraint->getTargets())) {
                    continue;
                }
                $constraints[ltrim($constraintClass, '\\')] = $constraint;
            }
        }

        $this->constraints = $constraints;

        return $this->constraints;
    }

    protected function getBundleNamespace($bundle)
    {
        try {
            $bundleObj = $this->getContainer()->get('kernel')->getBundle($bundle);
            $bundleNamespace = $bundleObj->getNamespace();
        } catch (\InvalidArgumentException $e) {
            $bundleNamespace = $bundle;
        }
    }

    protected function getEnumTypes()
    {
        $types = array_merge(Type::getTypesMap(), $this->getConfiguredTypes());

        $enumTypes = [];
        foreach ($types as $type => $class) {
            if (strpos(ltrim($class, '\\'), 'Doctrine\DBAL\Types') === 0) {
                continue;
            }
            if (is_subclass_of($class, '\Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType')) {
                $enumTypes[$type] = $class;
            }
        }
        return $enumTypes;
    }

    protected function getTypes()
    {
        $types = array_merge(Type::getTypesMap(), $this->getConfiguredTypes());
        $types = array_keys($types);

        $types = array_combine($types, array_fill(0, count($types), null));
        $aliases = $this->getTypeAliases();

        $types = array_merge($types, $aliases);

        return $types;
    }
    protected function getTypeAliases()
    {
        return [
            Type::TARRAY => 'a',
            Type::JSON_ARRAY => 'json',
            Type::BOOLEAN => 'b',
            Type::DATETIME => 'dt',
            Type::DATETIMETZ => 'dtz',
            Type::DATE => 'd',
            Type::TIME => 't',
            Type::INTEGER => 'i',
            Type::SMALLINT => 'si',
            Type::OBJECT => 'o',
            Type::STRING => 's',
            Type::TEXT => 'x',
            Type::FLOAT => 'fl',

            'one2one' => 'o2o',
            'many2one' => 'm2o',
            'many2many' => 'm2m',
            'one2many' => 'o2m',
            'enum' => 'e',
        ];
    }

    protected function getConfiguredTypes()
    {
        $param = 'doctrine.dbal.connection_factory.types';
        if (!$this->getContainer()->hasParameter($param)) {
            return [];
        }
        $types = [];
        foreach ($this->getContainer()->getParameter($param) as $type => $details) {
            $types[$type] = $details['class'];
        }
        return $types;
    }

    /**
     * @return \Doctrine\ORM\Mapping\ClassMetadata[]
     */
    protected function getExistingEntities()
    {
        $registry = $this->getContainer()->get('doctrine');
        $em = $registry->getManager();

        /** @var \Doctrine\ORM\Mapping\ClassMetadata[] $entityMetadata */
        $entityMetadata = $em->getMetadataFactory()->getAllMetadata();
        $bundleNamespaces = $this->getBundleNamespaces();

        $entities = [];
        foreach ($entityMetadata as $meta) {
            $namespace = $meta->getName();
            $bundle = $this->getBundle($namespace, $bundleNamespaces);
            $entities[$bundle . ':' . ClassFunctions::short($namespace)] = $meta;
        }

        return $entities;
    }

    protected function getBundleNamespaces()
    {
        $container = $this->getContainer();
        $bundles = $container->getParameter('kernel.bundles');

        $bundles = array_map(function ($namespace) {
            $namespace = rtrim($namespace, '\\');
            $namespace = substr($namespace, 0, strrpos($namespace, '\\'));
            return $namespace;
        }, $bundles);

        return $bundles;
    }

    protected function getBundle($entityClass, $bundles = [])
    {
        foreach ($bundles as $bundle => $bundleNamespace) {
            if (strpos($entityClass, $bundleNamespace) === 0) {
                return $bundle;
            }
        }
        return null;
    }

    protected function getEntityNormalizer($bundle, $existingEntityOptions)
    {
        return function ($entity) use ($bundle, $existingEntityOptions) {
            if (ctype_digit($entity) && isset($existingEntityOptions[$entity])) {
                return $existingEntityOptions[$entity];
            }
            if (strpos($entity, ':') === false) {
                $entity = $bundle . ':' . $entity;
            }
            return $entity;
        };
    }

    protected function outputCompactOptionsList(OutputInterface $output, array $options, $offset = 0, $maxWidth = 70)
    {
        $count = $offset;
        $i = 0;
        foreach ($options as $option => $alias) {
            if ($count > $maxWidth) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen(($alias ? $alias . ': ' : '') . $option);
            if ($alias !== null) {
                $output->write(sprintf('<info>%s</info>: ', $alias));
            }
            $output->write(sprintf('<comment>%s</comment>', $option));
            if (count($options) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
            $i++;
        }
        $output->writeln('');
    }
}
