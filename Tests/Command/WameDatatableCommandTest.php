<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

class WameDatatableCommandTest extends WameCommandTest
{
    public function setUp()
    {
        parent::setUp();
        //Create Book entity, so we can use this entity for the tests
        copy($this->getTestFilePath('Entity/BookDetailInfo.php'), $this->getResultFilePath('Entity/BookDetailInfo.php'));
    }

    /**
     * @dataProvider getCommandResultTestData
     */
    public function testCommandResult(array $options, array $compareFiles)
    {
        $executedCommandTester = $this->getExecutedCommandTester('wame:generate:datatable', $options);

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
                [
                    'Datatable/AppDatatable.php',
                    'Datatable/DatatableResultService.php',
                    'Datatable/BookDetailInfoDatatable.php',
                ]
            ],
        ];
    }
}