<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Wame\GeneratorBundle\MetaData\MetaEntity;

class WameVoterGenerator extends Generator
{
    public function generateByMetaEntity(MetaEntity $metaEntity, $allowOverride = false): bool
    {
        $securityDir = $this->getBundlePath($metaEntity->getBundle()).'/Security';

        //Add the AppVoter if it doesn't exist yet.
        $path = $securityDir.'/AppVoter.php';
        if (file_exists($path) === false) {
            $appVoterContent = $this->render('security/AppVoter.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appVoterContent);
        }

        $voterContent = $this->render('security/voter.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $securityDir.'/'.$metaEntity->getDirectory('/').$metaEntity->getEntityName().'Voter.php';

        return static::dump($path, $voterContent, $allowOverride) !== false;
    }
}
