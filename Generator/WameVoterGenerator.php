<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameVoterGenerator extends Generator
{
    public function generateByMetaEntity(MetaEntity $metaEntity)
    {
        //Add the AppVoter if it doesn't exist yet.
        $path = $metaEntity->getBundle()->getPath().'/Security/AppVoter.php';
        if (file_exists($path) === false) {
            $appVoterContent = $this->render('security/AppVoter.php.twig', [
                'bundle_namespace' => $metaEntity->getBundleNamespace(),
            ]);
            static::dump($path, $appVoterContent);
        }

        $voterContent = $this->render('security/voter.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Security/'.$metaEntity->getEntityName().'Voter.php';
        static::dump($path, $voterContent);

        return $path;
    }
}
