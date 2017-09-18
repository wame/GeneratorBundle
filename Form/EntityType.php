<?php

namespace Wame\SensioGeneratorBundle\Form;

use phpDocumentor\Reflection\DocBlock\Tags\Property;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;
use Wame\SensioGeneratorBundle\MetaData\MetaTrait;

class EntityType extends AbstractType
{
    //TODO: container isn't a neat way of doing this, so once completed, the specific services should be injected instead
    protected $container;

    protected $behaviourTraits = [
        'softdeleteable' => 'Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => 'Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => 'Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MetaEntity $metaEntity */
        $metaEntity = $builder->getData();
        $builder
            ->add('bundle', ChoiceType::class, [
                'label' => 'Bundle',
                'choices' => $this->getBundleOptions(),
                'choice_label' => function(BundleInterface $bundle) {
                    return $bundle->getName();
                },
            ])
            ->add('entityName', null, [
                'attr' => [
                    'class' => 'entity-name-option',
                ],
            ])
            ->add('tableName', null, [
                'attr' => [
                    'placeholder' => 'Provide Entity name first to have the table name automatically provided',
                    'class' => 'entity-table-option',
                ],
            ])
            ->add('traits', ChoiceType::class, [
                'required' => false,
                'choices' => $traitChoices = $this->getTraitOptions(),
                'choice_label' => function(MetaTrait $metaTrait) {
                    return $metaTrait->getName();
                },
                'expanded' => true,
                'multiple' => true,
                'data' => $metaEntity && $metaEntity->getEntityName() ? $metaEntity->getTraits()->toArray() : $traitChoices,
                'label_attr' => [
                    'class' => 'checkbox-inline',
                ]
            ])

            ->add('properties', CollectionType::class, [
                'entry_type' => PropertyType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'item', // we want to use 'tr.item' as collection elements' selector
                    ],
                ],
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype'    => true,
                'required'     => false,
                'by_reference' => true,
                'delete_empty' => true,
                'attr' => [
                    'class' => 'table entry-collection',
                ],
            ])

            ->add('generate_repository', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('generate_form', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('generate_crud', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('generate_datatable', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('generate_voter', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var MetaEntity $metaEntity */
           $metaEntity = $event->getData();
           foreach ($metaEntity->getProperties() as $property) {
               $property->setEntity($metaEntity);
           }

        });
    }

    protected function getTraitOptions()
    {
        $options = [];
        foreach ($this->behaviourTraits as $traitName => $traitNameSpace) {
            $options[] = (new MetaTrait())
                ->setName($traitName)
                ->setNamespace($traitNameSpace)
            ;
        }
        return $options;
    }

    protected function getBundleOptions()
    {
        $bundles = [];
        foreach ($this->container->get('kernel')->getBundles() as $bundle) {
            //Only show non-vendor bundles
            if (strpos($bundle->getPath(), 'vendor') === false) {
                $bundles[] = $bundle;
            }
        }
        return $bundles;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wame\SensioGeneratorBundle\MetaData\MetaEntity',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'wame_generator_entity';
    }


}
