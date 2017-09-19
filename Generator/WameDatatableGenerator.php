<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameDatatableGenerator extends Generator
{
    public function generate(MetaEntity $metaEntity, $allowOverride = false): bool
    {
        $this->addAppDatatable($metaEntity);
        $this->addDatatableResultService($metaEntity);

        $content = $this->render('datatable/datatable.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $path = $metaEntity->getBundle()->getPath().'/Datatable/'.$metaEntity->getEntityName().'Datatable.php';
        return static::dump($path, $content, $allowOverride) !== false;
    }

    protected function addAppDatatable(MetaEntity $metaEntity)
    {
        //Add the AppDatatable if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Datatable/AppDatatable.php';
        if (file_exists($path) === false) {
            $appDatatableContent = $this->render('datatable/AppDatatable.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appDatatableContent);
        }
    }

    protected function addDatatableResultService(MetaEntity $metaEntity)
    {
        //Add the DatatableResultService if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Datatable/DatatableResultService.php';
        if (file_exists($path) === false) {
            $appDatatableContent = $this->render('datatable/DatatableResultService.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appDatatableContent);
        }
    }
}
