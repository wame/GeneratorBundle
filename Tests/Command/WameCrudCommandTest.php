<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

class WameCrudCommandTest extends WameCommandTest
{
    public function setUp()
    {
        //Create Book entity, so we can use this entity for the tests
        $this->createExample('Entity/BookDetailInfo.php');
        //Create entity test file for using subdirectories
        $this->createExample('Entity/Admin/SpecialConfiguration.php');
        parent::setUp();
    }

    /**
     * @dataProvider getCommandResultTestData
     */
    public function testCommandResult(array $options, array $compareFiles)
    {
        $executedCommandTester = $this->getExecutedCommandTester('wame:generate:crud', $options);

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
                    'Controller/BookDetailInfoController.php',
                    'Datatable/AppDatatable.php',
                    'Datatable/DatatableResultService.php',
                    'Datatable/BookDetailInfoDatatable.php',
                    'Security/AppVoter.php',
                    'Security/BookDetailInfoVoter.php',
                    'Form/BookDetailInfoType.php',
                ]
            ],
            //Test subdirectories
            [
                ['entity' => 'WameGeneratorBundle:Admin\SpecialConfiguration','--overwrite' => true],
                [
                    'Controller/Admin/SpecialConfigurationController.php',
                    'Datatable/AppDatatable.php',
                    'Datatable/DatatableResultService.php',
                    'Datatable/Admin/SpecialConfigurationDatatable.php',
                    'Security/AppVoter.php',
                    'Security/Admin/SpecialConfigurationVoter.php',
                    'Form/Admin/SpecialConfigurationType.php',
                ]
            ],
        ];
    }
}