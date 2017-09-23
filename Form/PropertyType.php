<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Form;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wame\GeneratorBundle\MetaData\MetaValidation;

class PropertyType extends AbstractType
{
    //TODO: container isn't a neat way of doing this, so once completed, the specific services should be injected instead
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine($types = $this->getTypes(), $types),
                'attr' => [
                    'class' => 'property-type-option',
                    'data-role' => 'select2',
                ],
                'data' => 'string'
            ])
            ->add('nullable', CheckboxType::class, [
                'attr' => [
                    'class' => 'property-nullable-option',
                ],
            ])
            ->add('unique', CheckboxType::class, [
                'attr' => [
                    'class' => 'property-unique-option',
                ],
            ])
            ->add('displayField', CheckboxType::class, [
                'attr' => [
                    'class' => 'property-display-field-option',
                ],
            ])
            ->add('length', NumberType::class, [
                'data' => 255,
                'attr' => [
                    'class' => 'property-length-option',
                ],
            ])
            ->add('precision', NumberType::class, [
                'attr' => [
                    'class' => 'property-precision-option',
                ],
            ])
            ->add('scale', NumberType::class, [
                'attr' => [
                    'class' => 'property-scale-option',
                ],
            ])
            ->add('targetEntity', ChoiceType::class, [
                'label' => 'Target',
                'choices' => $this->getTargetEntityChoices(),
                'attr' => [
                    'class' => 'property-target-entity-option',
                ],
            ])
            ->add('enumType', ChoiceType::class, [
                'label' => 'Enum',
                'choices' => $this->getEnumTypes(),
                'attr' => [
                    'class' => 'property-enum-option',
                ],
            ])
            ->add('validations', ChoiceType::class, [
                'choices' => $this->getValidationOptions(),
                'choice_label' => function(MetaValidation $metaValidation) {
                    return $metaValidation->getType();
                },
                'multiple' => true,
                'attr' => [
                    'class' => 'property-validation-option',
                    'data-role' => 'select2',
                ],
            ])
        ;
    }

    protected function getValidationOptions()
    {
        $options = [];
        foreach ($this->getPropertyValidationConstraints() as $constraintName) {
            $options[] = (new MetaValidation())->setType($constraintName);
        }
        return $options;
    }

    /**
     * @return array|\Symfony\Component\Validator\Constraint[]
     */
    protected function getPropertyValidationConstraints()
    {
        $constraints = [];
        $componentNamespace = '\Symfony\Component\Validator\Constraints';

        // Try to get all default constraints by scanning the Component's Constraints folder
        $dir = dirname((new \ReflectionClass($componentNamespace.'\Valid'))->getFileName());
        foreach (scandir($dir, SCANDIR_SORT_ASCENDING ) as $file) {
            if (preg_match('/^(.+)(?!(Validator|Provider))\.php$/', $file, $matches)) {
                $constraintClass = $componentNamespace . '\\' . $matches[1];
                if (!is_subclass_of($constraintClass, '\Symfony\Component\Validator\Constraint')) {
                    continue;
                }
                $shortName = str_replace($componentNamespace.'\\', '', $constraintClass);
                $constraints[$shortName] = $shortName;
            }
        }
        return $constraints;
    }

    protected function getTargetEntityChoices()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Doctrine\ORM\Mapping\ClassMetadata[] $entityMetadata */
        $entityMetadata = $em->getMetadataFactory()->getAllMetadata();

        $entities = [];
        foreach ($entityMetadata as $meta) {
            $entityName = str_replace($meta->namespace.'\\', '', $meta->getName());
            $entities[$entityName] = $entityName;
        }

        return $entities;
    }

    protected function getEnumTypes()
    {
        $param = 'doctrine.dbal.connection_factory.types';
        if (!$this->container->hasParameter($param)) {
            return [];
        }
        $types = [];
        foreach ($this->container->getParameter($param) as $type => $details) {
            $types[$type] = $type; //$details['class'];
        }
        return $types;
    }

    protected function getTypes()
    {
        return [
            Type::TARRAY,
            Type::JSON_ARRAY,
            Type::BOOLEAN,
            Type::DATETIME,
            Type::DATETIMETZ,
            Type::DATE,
            Type::TIME,
            Type::INTEGER,
            Type::SMALLINT,
            Type::OBJECT,
            Type::STRING,
            Type::TEXT,
            Type::DECIMAL,
            'one2one',
            'many2one',
            'many2many',
            'one2many',
            'enum',
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wame\GeneratorBundle\MetaData\MetaProperty'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'wamePropertyType';
    }


}
