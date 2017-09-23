<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Symfony\Component\Console\Output\ConsoleOutput;
use Wame\GeneratorBundle\Twig\InflectorExtension;

class Generator
{
    protected $skeletonDirs;
    protected static $output;
    protected $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Sets an array of directories to look for templates.
     *
     * The directories must be sorted from the most specific to the most
     * directory.
     *
     * @param array $skeletonDirs An array of skeleton dirs
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);
    }

    protected function render($template, $parameters)
    {
        $twig = $this->getTwigEnvironment();

        return $twig->render($template, $parameters);
    }

    /**
     * Gets the twig environment that will render skeletons.
     *
     * @return \Twig_Environment
     */
    protected function getTwigEnvironment()
    {
        if (is_dir($dir = $this->rootDir.'/Resources/WameGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }
        $skeletonDirs[] = __DIR__.'/../Resources/skeleton';

        $this->setSkeletonDirs($skeletonDirs);

        $twigEnvironment = new \Twig_Environment(new \Twig_Loader_Filesystem($this->skeletonDirs), array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ));
        $twigEnvironment->addExtension(new InflectorExtension());

        return $twigEnvironment;
    }

    protected function renderFile($template, $target, $parameters)
    {
        self::mkdir(dirname($target));

        return self::dump($target, $this->render($template, $parameters));
    }

    public static function mkdir($dir, $mode = 0777, $recursive = true)
    {
        if (!is_dir($dir)) {
            mkdir($dir, $mode, $recursive);
            self::writeln(sprintf('  <fg=green>created</> %s', self::relativizePath($dir)));
        }
    }

    public static function dump($filename, $content, $allowOverwrite = true)
    {
        //TODO: set other permissions?
        $dir = dirname($filename);
        self::mkdir($dir);
        if (file_exists($filename)) {
            if ($allowOverwrite === false) {
                self::writeln(sprintf('  <fg=yellow>already exists</> %s', self::relativizePath($filename)));
                return false;
            }
            self::writeln(sprintf('  <fg=yellow>updated</> %s', self::relativizePath($filename)));
        } else {
            self::writeln(sprintf('  <fg=green>created</> %s', self::relativizePath($filename)));
        }

        return file_put_contents($filename, $content);
    }

    public static function append($filename, $content)
    {
        return file_put_contents($filename, $content, FILE_APPEND);
    }

    protected static function writeln($message)
    {
        if (null === self::$output) {
            self::$output = new ConsoleOutput();
        }

        self::$output->writeln($message);
    }

    protected static function relativizePath($absolutePath)
    {
        $relativePath = str_replace(getcwd(), '.', $absolutePath);

        return is_dir($absolutePath) ? rtrim($relativePath, '/').'/' : $relativePath;
    }
}
