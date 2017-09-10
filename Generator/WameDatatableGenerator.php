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

        //Add the AppDatatable if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Datatable/AppDatatable.php';
        if ($fs->exists($path) === false){
            $content = $this->render('datatable/AppDatatable.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            $fs->dumpFile($path, $content);
        }

        $content = $this->render('datatable/datatable.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Datatable/'.$metaEntity->getEntityName().'Datatable.php';
        $fs->dumpFile($path, $content);

        return $path;
    }
}
