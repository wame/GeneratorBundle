<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

class WameEnumCommandTest extends WameCommandTest
{
    /**
     * @dataProvider getCommandResultTestData
     */
    public function testCommandResult(array $options, array $compareFiles)
    {
        $executedCommandTester = $this->getExecutedCommandTester('wame:generate:enum', $options);

        $this->assertContains('Everything is OK!', $executedCommandTester->getDisplay());

        foreach ($compareFiles as $generatedFile) {
            $this->assertFileEqualsTestFile($generatedFile);
        }
    }

    public function getCommandResultTestData()
    {
        return [
            //Generate BookType
            [
                [
                    'enum' => 'WameGeneratorBundle:BookType',
                    '--options' => "fiction,FICTION,Fiction|non-fiction,NON_FICTION,Non fiction",
                    '--overwrite' => true,
                ],
                ['DBAL/Types/BookType.php']
            ],
            //Generate BookType without specifying bundle + using only enum-values
            [
                [
                    'enum' => 'BookType',
                    '--options' => "fiction|non-fiction",
                    '--overwrite' => true,
                ],
                ['DBAL/Types/BookType.php']
            ],
            //Generate BookType using array syntax
            [
                [
                    'enum' => 'BookType',
                    '--options' => "[
                        [fiction, FICTION, Fiction],
                        [non-fiction, NON_FICTION, Non fiction]
                    ]",
                    '--overwrite' => true,
                ],
                ['DBAL/Types/BookType.php']
            ],
        ];
    }

    public function testOverwritingBehaviour()
    {
        $this->getExecutedCommandTester('wame:generate:enum', [
            'enum' => 'BookType', '--options' => "fiction|non-fiction", '--overwrite' => true,
        ]);
        $this->assertFileEqualsTestFile('DBAL/Types/BookType.php');

        //The same booktype should not be changed without overwrite option
        $this->getExecutedCommandTester('wame:generate:enum', [
            'enum' => 'BookType', '--options' => "fantasy|action"
        ]);
        $this->assertFileEqualsTestFile('DBAL/Types/BookType.php');

        //The same booktype should be changed with overwrite option
        $this->getExecutedCommandTester('wame:generate:enum', [
            'enum' => 'BookType', '--options' => "fantasy|action", '--overwrite' => true,
        ]);
        $this->assertFileNotEqualsTestFile('DBAL/Types/BookType.php');
    }


    public function testInteractiveCommandResult()
    {
        $commandTester = $this->getExecutedCommandTester('wame:generate:enum', [],
            //Enum name, should end with Type:
            "School\n"
            //Enum name, should end with Type:
            ."SchoolType\n"
            //New option value [as persisted] (press <return> to stop adding values):
            ."primary\n"
            //Constant for this option [PRIMARY]:
            ."\n"
            //Label for this option [Primary]:
            ."Primary school\n"
            //New option value [as persisted] (press <return> to stop adding values):
            ."special-education\n"
            //Constant for this option [SPECIAL_EDUCATION]:
            ."SPECIAL_EDUCATION\n"
            //Label for this option [Special education]:
            ."\n"
            //New option value [as persisted] (press <return> to stop adding values):
            ."\n"
            //Confirm automatic update of the config [yes]?
            ."yes\n"

        );
        $commandDisplay = $commandTester->getDisplay();

        $this->assertContains('Enum name, should end with Type:', $commandDisplay);
        $this->assertContains('The enum name must end with Type.', $commandDisplay);
        $this->assertContains('Constant for this option [SPECIAL_EDUCATION]', $commandDisplay);
        $this->assertContains('Label for this option [Special education]', $commandDisplay);
        $this->assertContains('Registering the doctrine enum type:', $commandDisplay);
        //TODO: assert that the type is actually registered to config.yml
        $this->assertContains('Everything is OK!', $commandDisplay);

        $this->assertFileEqualsTestFile('DBAL/Types/SchoolType.php');
    }
}