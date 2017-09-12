<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameDatatableGenerator extends Generator
{
    use WameGeneratorTrait;

    public function generate(MetaEntity $metaEntity)
    {
        $fs = new Filesystem();

        $this->addAppDatatable($fs, $metaEntity);
        $this->addDatatableResultService($fs, $metaEntity);

        $content = $this->render('datatable/datatable.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $path = $metaEntity->getBundle()->getPath().'/Datatable/'.$metaEntity->getEntityName().'Datatable.php';
        $fs->dumpFile($path, $content);

        return $path;
    }

    protected function addAppDatatable(Filesystem $fs, MetaEntity $metaEntity)
    {
        //Add the AppDatatable if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Datatable/AppDatatable.php';
        if ($fs->exists($path) === false) {
            $appDatatableContent = $this->render('datatable/AppDatatable.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            $fs->dumpFile($path, $appDatatableContent);
        }
    }

    protected function addDatatableResultService(Filesystem $fs, MetaEntity $metaEntity)
    {
        //Add the DatatableResultService if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Datatable/DatatableResultService.php';
        if ($fs->exists($path) === false) {
            $appDatatableContent = $this->render('datatable/DatatableResultService.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            $fs->dumpFile($path, $appDatatableContent);
        }
    }
}
