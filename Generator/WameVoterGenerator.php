<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameVoterGenerator extends Generator
{
    use WameGeneratorTrait;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function generateByMetaEntity(MetaEntity $metaEntity)
    {
        $fs = new Filesystem();

        //Add the AppVoter if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Security/AppVoter.php';
        if ($fs->exists($path) === false) {
            $appVoterContent = $this->render('security/AppVoter.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            $fs->dumpFile($path, $appVoterContent);
        }

        $voterContent = $this->render('security/voter.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Security/'.$metaEntity->getEntityName().'Voter.php';
        $fs->dumpFile($path, $voterContent);

        return $path;
    }
}
