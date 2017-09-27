<?php
declare(strict_types=1);

/*
 * This file is a modified copy of the DoctrineCrudGenerator that is part of the Symfony package.
 */

namespace Wame\GeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Inflector\Inflector;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kevin Driessen <kevin@wame.nl>
 */
class DoctrineCrudGenerator extends Generator
{
    protected $rootDir;
    protected $routePrefix;
    protected $routeNamePrefix;
    protected $bundle;
    protected $entity;
    protected $entitySingularized;
    protected $entityPluralized;
    protected $metadata;
    protected $actions;
    protected $useDatatable;
    protected $useVoter;

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface   $bundle           A bundle object
     * @param string            $entity           The entity relative class name
     * @param ClassMetadataInfo $metadata         The entity class metadata
     * @param string            $routePrefix      The route name prefix
     * @param bool              $needWriteActions Whether or not to generate write actions
     * @param bool              $forceOverwrite   Whether or not to overwrite the controller
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $routePrefix, $needWriteActions, $forceOverwrite, $useDatatable, $useVoter)
    {
        $this->useDatatable = $useDatatable;
        $this->useVoter = $useVoter;
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = self::getRouteNamePrefix($routePrefix);
        $this->actions = $needWriteActions ? array('index', 'show', 'new', 'edit', 'delete') : array('index', 'show');

        if (count($metadata->identifier) !== 1) {
            throw new \RuntimeException('The CRUD generator does not support entity classes with multiple or no primary keys.');
        }

        $this->entity = $entity;
        $entity = str_replace('\\', '/', $entity);
        $entityParts = explode('/', $entity);
        $entityName = end($entityParts);
        $this->entitySingularized = lcfirst(Inflector::singularize($entityName));
        $this->entityPluralized = lcfirst(Inflector::pluralize($entityName));
        $this->bundle = $bundle;
        $this->metadata = $metadata;

        $this->generateControllerClass($forceOverwrite);

        $dir = sprintf('%s/Resources/views/%s', $this->rootDir, Inflector::tableize($entity));

        if (!file_exists($dir)) {
            self::mkdir($dir);
        }

        $this->generateIndexView($dir);

        if (in_array('show', $this->actions, true)) {
            $this->generateShowView($dir);
        }

        if (in_array('new', $this->actions, true)) {
            $this->generateNewView($dir);
        }

        if (in_array('edit', $this->actions, true)) {
            $this->generateEditView($dir);
        }
    }

    protected function generateControllerClass(bool $forceOverwrite): void
    {
        $dir = $this->bundle->getPath();

        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (!$forceOverwrite && file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $this->renderFile('crud/controller.php.twig', $target, [
            'actions' => $this->actions,
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle' => $this->bundle->getName(),
            'entity' => $this->entity,
            'entity_singularized' => $this->entitySingularized,
            'entity_pluralized' => $this->entityPluralized,
            'identifier' => $this->metadata->identifier[0],
            'entity_class' => $entityClass,
            'namespace' => $this->bundle->getNamespace(),
            'entity_namespace' => $entityNamespace,
            'format' => 'annotation',
            // BC with Symfony 2.7
            'use_form_type_instance' => !method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix'),
            'use_datatable' => $this->useDatatable,
            'use_voter' => $this->useVoter,
        ]);
    }

    protected function generateIndexView(string $dir): void
    {
        $templateFile = $this->useDatatable ? 'crud/views/index-with-datatable.html.twig.twig' : 'crud/views/index.html.twig.twig';
        $this->renderFile($templateFile, $dir.'/index.html.twig', [
            'bundle' => $this->bundle->getName(),
            'entity' => $this->entity,
            'entity_pluralized' => $this->entityPluralized,
            'entity_singularized' => $this->entitySingularized,
            'identifier' => $this->metadata->identifier[0],
            'fields' => $this->metadata->fieldMappings,
            'actions' => $this->actions,
            'record_actions' => $this->getRecordActions(),
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'use_voter' => $this->useVoter,
        ]);
    }

    protected function generateShowView(string $dir): void
    {
        $this->renderFile('crud/views/show.html.twig.twig', $dir.'/show.html.twig', [
            'bundle' => $this->bundle->getName(),
            'entity' => $this->entity,
            'entity_singularized' => $this->entitySingularized,
            'identifier' => $this->metadata->identifier[0],
            'fields' => $this->metadata->fieldMappings,
            'actions' => $this->actions,
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'use_voter' => $this->useVoter,
        ]);
    }

    protected function generateNewView(string $dir): void
    {
        $this->renderFile('crud/views/new.html.twig.twig', $dir.'/new.html.twig', [
            'bundle' => $this->bundle->getName(),
            'entity' => $this->entity,
            'entity_singularized' => $this->entitySingularized,
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'actions' => $this->actions,
            'fields' => $this->metadata->fieldMappings,
            'use_voter' => $this->useVoter,
        ]);
    }

    protected function generateEditView(string $dir): void
    {
        $this->renderFile('crud/views/edit.html.twig.twig', $dir.'/edit.html.twig', [
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'identifier' => $this->metadata->identifier[0],
            'entity' => $this->entity,
            'entity_singularized' => $this->entitySingularized,
            'fields' => $this->metadata->fieldMappings,
            'bundle' => $this->bundle->getName(),
            'actions' => $this->actions,
            'use_voter' => $this->useVoter,
        ]);
    }

    protected function getRecordActions(): array
    {
        return array_filter($this->actions, function ($item) {
            return in_array($item, ['show', 'edit']);
        });
    }

    public static function getRouteNamePrefix(string $prefix): string
    {
        $prefix = preg_replace('/{(.*?)}/', '', $prefix); // {foo}_bar -> _bar
        $prefix = str_replace('/', '_', $prefix);
        $prefix = preg_replace('/_+/', '_', $prefix);     // foo__bar -> foo_bar
        $prefix = trim($prefix, '_');

        return $prefix;
    }
}
