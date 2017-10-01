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
}