<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameVoterGenerator extends Generator
{
    public function generateByMetaEntity(MetaEntity $metaEntity, $allowOverride = false): bool
    {
        $securityDir = $metaEntity->getBundle()->getPath().'/Security';

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

        $path = $securityDir.'/'.$metaEntity->getEntityName().'Voter.php';

        return static::dump($path, $voterContent, $allowOverride) !== false;
    }
}
