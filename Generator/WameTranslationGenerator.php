<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Wame\SensioGeneratorBundle\Inflector\Inflector;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameTranslationGenerator
{
    public function updateTranslationsByMetaEntity(MetaEntity $metaEntity)
    {
        $messageArray = [];

        $tableizedEntityName = Inflector::tableize($metaEntity->getEntityName());
        $humanizedEntityName = Inflector::humanize($metaEntity->getEntityName());

        $messageArray[ucfirst($tableizedEntityName)] = ucfirst($humanizedEntityName);
        $messageArray[ucfirst(Inflector::pluralize($tableizedEntityName))] = ucfirst(Inflector::pluralize($humanizedEntityName));
        $messageArray[$tableizedEntityName] = [
            'index_title' => $humanizedEntityName. ' overview',
            'new_title' => 'Create ' . $humanizedEntityName,
            'edit_title' => 'Edit ' . $humanizedEntityName,
            'show_title' => 'View ' . $humanizedEntityName,
            'create_success' => $humanizedEntityName. ' created',
            'edit_success' => $humanizedEntityName. ' updated',
            'delete_success' => $humanizedEntityName. ' removed',
        ];
        foreach ($metaEntity->getProperties() as $metaProperty) {
            $propertyName = $metaProperty->getName();
            $messageArray[$tableizedEntityName][Inflector::tableize($propertyName)] = Inflector::humanize($propertyName);
        }

        //TODO: or retrieve the path by kernel instead.
        $path = $metaEntity->getBundle()->getPath().'/../../app/Resources/translations/messages.en.yml';
        $fs = new Filesystem();
        if ($fs->exists($path)) {
            $originalMessageArray = Yaml::parse(file_get_contents($path));
            $messageArray = array_replace_recursive($messageArray, $originalMessageArray);
        }

        $yaml = Yaml::dump($messageArray);

        $fs->dumpFile($path, $yaml);
    }
}
