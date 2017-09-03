<?php

/*
 * Instead of extending the 'DoctrineEntityGenerator', this base class is used
 * to add functionality which can be called in the DoctrineEntityGenerator.
 */

namespace Wame\SensioGeneratorBundle\Generator;


use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Console\Input\InputInterface;

abstract class WameEntityGenerator extends Generator
{
    /**
     * @var array
     */
    protected $behaviourTraits = [
        'softdeleteable' => '\\Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => '\\Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => '\\Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    protected $softdeleteableFieldName = 'deletedAt';

    /** @return EntityGenerator */
    abstract protected function getEntityGenerator();

    protected function addSettingsToGenerator(EntityGenerator $entityGenerator, array $fields, InputInterface $input)
    {
        foreach ($fields as $field) {
            $enumType = $field['enumType'] ?? null;
            $enumTypeClass = $field['enumTypeClass'] ?? null;
            if ($enumType) {
                $entityGenerator->setEnumType($enumType, $enumTypeClass);
            }
        }

        if ($input->hasOption('displayField')) {
            $entityGenerator->setDisplayField($input->getOption('displayField'));
        }

        if ($input->hasOption('behaviours')) {
            $behaviours = $input->getOption('behaviours');
            foreach ($behaviours as $behaviour) {
                switch ($behaviour) {
                    case 'softdeleteable':
                        $entityGenerator->addNamespaceImport('Gedmo\\Mapping\\Annotation', 'Gedmo');
                        $entityGenerator->addClassAnnotation('@Gedmo\\SoftDeleteable(fieldName="'.$this->softdeleteableFieldName.'", timeAware=false)');

                        //TODO: how are we gonna apply the config.yml settings to override the trait?
                        $entityGenerator->addNamespaceImport('Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity', 'SoftDeleteable');
                        $entityGenerator->addTrait('SoftDeleteable');
                        break;
                    case 'blameable':
                        //TODO: how are we gonna apply the config.yml settings to override the trait?
                        $entityGenerator->addNamespaceImport('Gedmo\\Blameable\\Traits\\BlameableEntity', 'Blameable');
                        $entityGenerator->addTrait('Blameable');
                        break;
                    case 'timestampable':
                        //TODO: how are we gonna apply the config.yml settings to override the trait?
                        $entityGenerator->addNamespaceImport('Gedmo\\Timestampable\\Traits\\TimestampableEntity', 'Timestampable');
                        $entityGenerator->addTrait('Timestampable');
                        break;
                    default:
                        throw new \InvalidArgumentException(sprintf("Unknown behaviour '%s'"));
                }
            }
        }
    }

    protected function mapField($field, ClassMetadataInfo $class)
    {
        $entityGenerator = $this->getEntityGenerator();
        switch ($field['type']) {
            case 'many2one':
                $class->mapManyToOne($field);
                break;
            case 'many2many':
                $class->mapManyToMany($field);
                break;
            case 'one2one':
                $class->mapOneToOne($field);
                break;
            case 'one2many':
                $class->mapOneToMany($field);
                break;
            case 'enum':
                $field['type'] = $field['enumType'];
                $class->mapField($field);
                $entityGenerator->setEnumType($field['enumType'], $field['enumTypeClass']);
                break;
            default:
                $class->mapField($field);
                if (isset($field['validation'])) {
                    $entityGenerator->setFieldValidation($field['fieldName'], $field['validation']);
                }
        }
    }
}
