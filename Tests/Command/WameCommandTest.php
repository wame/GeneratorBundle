<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class WameCommandTest extends KernelTestCase
{
    /** @var Application */
    protected static $application;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $application = new Application(static::$kernel);
        static::$application = $application;
    }

    protected function getExecutedCommandTester(string $commandName, array $options, string $input = '')
    {
        $command = static::$application->find($commandName);
        $command->setApplication(static::$application);

        $commandTester = new CommandTester($command);
        $this->setInputs($commandTester, $command, $input);
        $commandTester->execute($options);

        return $commandTester;
    }

    protected function getResultFilePath($fileName)
    {
        return  __DIR__.'/../../'.$fileName;
    }

    protected function getTestFilePath($fileName)
    {
        return  __DIR__ . '/../../Tests/Command/ExpectedResults/' .$fileName.'.txt';
    }

    protected function createExample($fileName)
    {
        $filePath = $this->getTestFilePath($fileName);
        $targetpath = $this->getResultFilePath($fileName);
        $directory = dirname($targetpath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        copy($filePath, $this->getResultFilePath($fileName));
    }

    protected function assertFileEqualsTestFile(string $fileRelativePath)
    {
        $this->assertFileEquals($this->getTestFilePath($fileRelativePath), $this->getResultFilePath($fileRelativePath));
    }

    protected function assertFileNotEqualsTestFile(string $fileRelativePath)
    {
        $this->assertFileNotEquals($this->getTestFilePath($fileRelativePath), $this->getResultFilePath($fileRelativePath));
    }

    /**
     * Copied from Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateCommandTest
     */
    protected function setInputs(CommandTester $tester, Command $command, string $input = ''): void
    {
        $input .= str_repeat("\n", 10);
        if (method_exists($tester, 'setInputs')) {
            $tester->setInputs(explode("\n", $input));
        } else {
            $stream = fopen('php://memory', 'r+', false);
            fwrite($stream, $input);
            rewind($stream);

            $command->getHelperSet()->get('question')->setInputStream($stream);
        }
    }
}