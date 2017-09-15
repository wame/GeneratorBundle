<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Wame\SensioGeneratorBundle\Inflector\Inflector;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameTranslationGenerator extends Generator
{
    use WameGeneratorTrait;

    protected $translationsDir;
    protected $locale;

    public function __construct(string $translationsDir, string $locale)
    {
        $this->translationsDir = $translationsDir;
        $this->locale = $locale;
    }

    public function updateByMetaEntity(MetaEntity $metaEntity)
    {
        $path = $this->getMessagesPath();
        $fs = new Filesystem();

        if ($fs->exists($path) === false) {
            $messagesFile = $this->render('translations/messages.'.$this->locale.'.yml.twig', []);
            $fs->dumpFile($path, $messagesFile);
        } else {
            $originalMessageArray = Yaml::parse(file_get_contents($path));
            //Only add to message-file if there hasn't been set anything for this entity yet
            if (array_key_exists(Inflector::tableize($metaEntity->getEntityName()), $originalMessageArray)) {
                return;
            }
        }

        $addMessagesFile = $this->render('translations/_add_messages.'.$this->locale.'.yml.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $fs->appendToFile($path, $addMessagesFile);
    }

    protected function getMessagesPath(): string
    {
        return $this->translationsDir.'/messages.'.$this->locale.'.yml';
    }
}
