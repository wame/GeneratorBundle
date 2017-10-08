<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

class WameFormCommandTest extends WameCommandTest
{
    public function setUp()
    {
        $this->createExample('Entity/BookDetailInfo.php');
        parent::setUp();
    }

    /**
     * @dataProvider getCommandResultTestData
     */
    public function testCommandResult(array $options, array $compareFiles)
    {
        $executedCommandTester = $this->getExecutedCommandTester('wame:generate:form', $options);

        $this->assertContains('Everything is OK!', $executedCommandTester->getDisplay());

        foreach ($compareFiles as $generatedFile) {
            $this->assertFileEqualsTestFile($generatedFile);
        }
    }

    public function getCommandResultTestData()
    {
        return [
            [
                ['entity' => 'WameGeneratorBundle:BookDetailInfo','--overwrite' => true],
                ['Form/BookDetailInfoType.php']
            ],
        ];
    }
}