<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command\Helper;

use Doctrine\DBAL\Types\Type;
use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraint;
use Wame\SensioGeneratorBundle\Command\WameValidators;

class EntityQuestionHelper extends QuestionHelper
{
    const MAX_OUTPUT_WIDTH = 70;

    /** @var RegistryInterface */
    protected $registry;

    /** @var array */
    protected $bundles;

    /** @var array */
    protected $configuredTypes = [];

    protected $behaviours = [
        'timestampable' => true,
        'blameable' => true,
        'softdeleteable' => true,
    ];

    protected $constraints;

    protected static $typeAliases = [
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

    public function __construct(RegistryInterface $registry, ?array $bundles, array $configuredTypes)
    {
        $this->registry = $registry;
        $this->bundles = $bundles;
        foreach ($configuredTypes as $type => $details) {
            $this->configuredTypes[$type] = $details['class'];
        }
    }

    public function askBehaviours(InputInterface $input, OutputInterface $output)
    {
        // ask about timestampable/blameable/softdeletable behaviours
        $behavioursStr = '<comment>'.implode(', ', array_keys($this->behaviours)).'</comment>';
        $allBehaviours = (!$input->getOption('no-blameable') && !$input->getOption('no-timestampable') && !$input->getOption('no-softdeleteable'));
        $questionAll = new ConfirmationQuestion($this->getQuestion('Add default behaviours ('.$behavioursStr.')', $allBehaviours ? 'yes' : 'no'), $allBehaviours);
        if ($this->ask($input, $output, $questionAll)) {
            $behaviours = array_keys($this->behaviours);
        } else {
            $behaviours = [];
            foreach ($this->behaviours as $behaviour => $enabled) {
                $enabled = $input->getOption('no-'.$behaviour) ? false : $enabled;
                $question = new ConfirmationQuestion($this->getQuestion(sprintf('Add <comment>%s</comment> behaviour', $behaviour), $enabled ? 'yes' : 'no'), $enabled);
                if ($this->ask($input, $output, $question)) {
                    $behaviours[] = $behaviour;
                }
            }
        }

        $input->setOption('behaviours', $behaviours);
    }

    public function askDisplayField(InputInterface $input, OutputInterface $output)
    {
        $entityFields = $input->getOption('fields');
        //Do not ask questions if display-field is already set
        if ($input->hasOption('display-field') && array_key_exists($input->getOption('display-field'), $entityFields)) {
            return;
        }

        $output->writeln([
            '',
            'If possible we should add a __toString method, you can do this by picking a "display field"',
            ''
        ]);
        $displayFieldOptions = array_keys(array_filter($entityFields, function ($field) {
            //To avoid too much complexity, relations aren't options for display field.
            return !in_array($field['type'], ['many2one', 'one2many', 'many2many', 'one2one'], true);
        }));

        $displayFieldOptions = $displayFieldOptions ? array_combine(range(1, count($displayFieldOptions)), $displayFieldOptions) : [];

        $output->write('<info>Available fields:</info> ');
        $this->outputCompactOptionsList($output, array_flip($displayFieldOptions), 20);

        $defaultField = null;
        foreach ($entityFields as $fieldName => $field) {
            if (strpos($fieldName, 'title') !== false || strpos($fieldName, 'name') !== false) {
                $defaultField = $fieldName;
                break;
            }
        }

        $question = new Question($this->getQuestion('Which field you want to use? (leave empty to skip)', $defaultField), $defaultField);
        $question->setAutocompleterValues(array_keys($displayFieldOptions));
        $question->setValidator(WameValidators::getDisplayFieldValidator($displayFieldOptions));

        $displayField = $this->ask($input, $output, $question);

        if ($displayField) {
            $entityFields[$displayField]['displayField'] = true;
            $input->setOption('fields', $entityFields);
            $input->setOption('display-field', $displayField);
        }
    }

    public function askFieldName(InputInterface $input, OutputInterface $output, $fields): ?string
    {
        $question = new Question($this->getQuestion('New field name (press <return> to stop adding fields)', null), null);
        $question->setValidator(WameValidators::getFieldNameValidator($fields));

        return $this->ask($input, $output, $question);
    }

    public function askFieldType(InputInterface $input, OutputInterface $output, $columnName): ?string
    {
        $types = $this->getTypes();
        $this->outputCompactOptionsList($output, $types);
        $typeOptions = array_keys($types);

        $defaultType = $this->guessFieldType($columnName);

        $question = new Question($this->getQuestion('Field type', $defaultType), $defaultType);
        $question->setNormalizer(WameValidators::getTypeNormalizer($types));
        $question->setValidator(WameValidators::getTypeValidator($typeOptions));
        $question->setAutocompleterValues(array_merge($typeOptions, static::$typeAliases));
        return $this->ask($input, $output, $question);
    }

    public function askFieldLength(InputInterface $input, OutputInterface $output): ?int
    {
        $question = new Question($this->getQuestion('Field length', 255), 255);
        $question->setValidator(WameValidators::getLengthValidator());
        return $this->ask($input, $output, $question);
    }

    public function askFieldPrecision(InputInterface $input, OutputInterface $output): ?int
    {
        // 10 is the default value given in \Doctrine\DBAL\Schema\Column::$_precision
        $question = new Question($this->getQuestion('Precision', 10), 10);
        $question->setValidator(WameValidators::getPrecisionValidator());
        return (int) $this->ask($input, $output, $question);
    }

    public function askFieldScale(InputInterface $input, OutputInterface $output): ?int
    {
        // 0 is the default value given in \Doctrine\DBAL\Schema\Column::$_scale
        $question = new Question($this->getQuestion('Scale', 0), 0);
        $question->setValidator(WameValidators::getScaleValidator());
        return (int) $this->ask($input, $output, $question);
    }

    public function askFieldNullable(InputInterface $input, OutputInterface $output): ?bool
    {
        $question = new Question($this->getQuestion('Is nullable', 'false'), false);
        $question->setValidator(WameValidators::getBoolValidator());
        $question->setAutocompleterValues(['true', 'false']);
        return $this->ask($input, $output, $question);
    }

    public function askFieldUnique(InputInterface $input, OutputInterface $output): ?bool
    {
        $question =  new Question($this->getQuestion('Unique', 'false'), false);
        $question->setValidator(WameValidators::getBoolValidator());
        $question->setAutocompleterValues(['true', 'false']);
        return $this->ask($input, $output, $question);
    }

    public function askTargetEntity(InputInterface $input, OutputInterface $output, string $bundleName): ?string
    {
        $existingEntities = $this->getExistingEntities();

        $existingEntityOptions = !empty($existingEntities)
            ? array_combine(range(1, count($existingEntities)), array_keys($existingEntities))
            : [];


        $this->outputCompactOptionsList($output, array_flip($existingEntityOptions));

        //TODO: add default value to show suggestion based on column-name
        $question = new Question($this->getQuestion('Related Entity', null), null);
        $question->setAutocompleterValues(array_keys($existingEntities));
        $question->setNormalizer(WameValidators::getEntityNormalizer($bundleName, $existingEntityOptions));
        // TODO: should we add a validator that checks if the entity exists?
        return $this->ask($input, $output, $question);
    }

    public function askReferenceColumnName(InputInterface $input, OutputInterface $output, $targetEntity): ?string
    {
        $existingEntities = $this->getExistingEntities();
        $refColumn = 'id';
        if (isset($existingEntities[$targetEntity])) {
            $targetEntityMeta = $existingEntities[$targetEntity];
            try {
                $refColumn = $targetEntityMeta->getSingleIdentifierColumnName();
            } catch (\Exception $e) {
                // do nothing
            }
        }
        $question = new Question($this->getQuestion('Reference column for related Entity', $refColumn), $refColumn);
        return $this->ask($input, $output, $question);
    }

    public function askFieldEnumType(InputInterface $input, OutputInterface $output): ?string
    {
        $enumTypes = $this->getEnumTypes();
        $enumOptionsList = [ 'Create a new Enum Type' => 0 ];
        if (count($enumTypes) > 0) {
            $enumOptionsList = array_merge(
                $enumOptionsList,
                array_combine(array_keys($enumTypes), range(1, count($enumTypes)))
            );
        }

        $question = new Question($this->getQuestion('Which enum type', ''), '');
        $question->setAutocompleterValues(array_keys($enumTypes));
        $question->setValidator(WameValidators::getEnumTypeValidator($enumOptionsList));

        return $this->ask($input, $output, $question);
    }

    public function askFieldValidations(InputInterface $input, OutputInterface $output): ?array
    {
        if ($input->hasOption('no-validation') && $input->getOption('no-validation')) {
            return null;
        }
        $output->writeln([
            'It\'s a good idea to add validation to your entity properties!',
            'Check the documentation for more into:',
            'http://symfony.com/doc/current/book/validation.html',
            '',
            'These are the default available constraints:'
        ]);

        $fieldConstraints = [];

        $constraints = $this->getPropertyValidationConstraints();
        $constraintClasses = [];
        foreach ($constraints as $constraintClass => $constraint) {
            $name = substr($constraintClass, strrpos($constraintClass, '\\') + 1);
            $constraintClasses[$constraintClass] = $name;
        }
        $constraintOptions = empty($constraints) ? [] : array_combine(range(1, count($constraints)), $constraintClasses);

        $this->outputCompactOptionsList($output, array_flip($constraintOptions));

        while (true) {
            $output->writeln('');
            $question = new Question($this->getQuestion('Add validation (press <return> to stop adding)', null));
            $question->setValidator(WameValidators::getConstraintValidator($constraintOptions));
            $type = $this->ask($input, $output, $question);

            if (!$type) {
                break;
            }

            $fieldConstraints[] = [
                'type' => $type,
                'options' => [],
            ];

            $currentConstraints = array_map(function ($fieldConstraint) {
                return $fieldConstraint['type'];
            }, $fieldConstraints);
            $output->writeln('Current validations: '. implode(', ', $currentConstraints));
        }

        return $fieldConstraints;
    }

    /**
     * @return Constraint[]
     */
    protected function getPropertyValidationConstraints(): array
    {
        //TODO: perhaps we should use an array with predefined constrains instead, so that we only show constraints that make any sense.
        //TODO: also, using predefined constraints, we can add options such as 'today' or 'greater than zero', etc
        if ($this->constraints) {
            return $this->constraints;
        }

        $constraints = [];
        $componentNamespace = '\Symfony\Component\Validator\Constraints';

        // Try to get all default constraints by scanning the Component's Constraints folder
        $dir = dirname((new \ReflectionClass($componentNamespace.'\Valid'))->getFileName());
        foreach (scandir($dir, SCANDIR_SORT_ASCENDING) as $file) {
            if (preg_match('/^(.+)(?!(Validator|Provider))\.php$/', $file, $matches)) {
                $constraintClass = $componentNamespace . '\\' . $matches[1];
                if (!is_subclass_of($constraintClass, Constraint::class)) {
                    continue;
                }
                $ref = new \ReflectionClass($constraintClass);
                if ($ref->isAbstract()) {
                    continue;
                }

                /** @var Constraint $constraint */
                try {
                    $constraint = new $constraintClass();
                } catch (\Exception $e) {
                    continue;
                }
                if (!in_array(Constraint::PROPERTY_CONSTRAINT, (array)$constraint->getTargets(), true)) {
                    continue;
                }
                $constraints[ltrim($constraintClass, '\\')] = $constraint;
            }
        }

        $this->constraints = $constraints;

        return $this->constraints;
    }

    protected function getTypes()
    {
        $types = array_merge(Type::getTypesMap(), $this->configuredTypes);
        $types = array_keys($types);
        $types = array_combine($types, array_fill(0, count($types), null));
        $aliases = static::$typeAliases;
        $types = array_merge($types, $aliases);
        return $types;
    }

    protected function getEnumTypes()
    {
        $enumTypes = [];
        foreach ($this->configuredTypes as $type => $typeClass) {
            if (strpos(ltrim($typeClass, '\\'), 'Doctrine\DBAL\Types') === 0) {
                continue;
            }
            if (is_subclass_of($typeClass, AbstractEnumType::class)) {
                $enumTypes[$type] = $typeClass;
            }
        }
        return $enumTypes;
    }

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

    protected function outputAvailableEntities(OutputInterface $output, array $existingEntityOptions)
    {
        $output->write('<info>Available Entities:</info> ');
        $this->outputCompactOptionsList($output, array_flip($existingEntityOptions));
        $output->writeln('');
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

    protected function guessFieldType(string $columnName): string
    {
        $lastThreeChars = substr($columnName, -3);
        $lastFourChars = substr($columnName, -4);
        $lastFiveChars = substr($columnName, -5);
        if ($lastThreeChars === '_at' || $lastThreeChars === '_on') {
            return 'datetime';
        } if ($lastThreeChars === '_id' || $lastFiveChars === 'count') {
            return 'integer';
        } if (0 === strpos($columnName, 'is_') || 0 === strpos($columnName, 'has_')) {
            return 'boolean';
        } if ($lastFourChars === 'date') {
            return 'date';
        } if ($lastThreeChars === '_id') {
            return 'many2one';
        } if (in_array($columnName, ['summary', 'description', 'text'], true)) {
            return 'text';
        } if ($lastFiveChars === 'price') {
            return 'decimal';
        }
        return 'string';
    }
}
