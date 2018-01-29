<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Wame\GeneratorBundle\MetaData\MetaEntity;

class WameDatatableGenerator extends Generator
{
    public function generate(MetaEntity $metaEntity, $allowOverride = false): bool
    {
        $datatableDir = $this->getBundlePath($metaEntity->getBundle()).'/Datatable';

        $this->addAppDatatable($metaEntity, $datatableDir);
        $this->addDatatableResultService($metaEntity, $datatableDir);

        $content = $this->render('datatable/datatable.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $path = $datatableDir.'/'.$metaEntity->getDirectory('/').$metaEntity->getEntityName().'Datatable.php';
        return static::dump($path, $content, $allowOverride) !== false;
    }

    protected function addAppDatatable(MetaEntity $metaEntity, string $datatableDir)
    {
        //Add the AppDatatable if it doesn't exist yet.
        $path = $datatableDir.'/AppDatatable.php';
        if (file_exists($path) === false) {
            $appDatatableContent = $this->render('datatable/AppDatatable.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appDatatableContent);
        }
    }

    protected function addDatatableResultService(MetaEntity $metaEntity, string $datatableDir)
    {
        //Add the DatatableResultService if it doesn't exist yet.
        $path = $datatableDir.'/DatatableResultService.php';
        if (file_exists($path) === false) {
            $appDatatableContent = $this->render('datatable/DatatableResultService.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appDatatableContent);
        }
    }
}
