<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command;

use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityAdminSpecialConfiguration;
use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityAuthor;
use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityBook;
use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityBookDetailInfo;
use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityLibrary;
use Wame\GeneratorBundle\Tests\Command\CommandDataFiles\EntityPerson;

class WameEntityCommandTest extends WameCommandTest
{
    /**
     * @dataProvider getCommandData
     */
    public function testInteractiveCommand($options, $compareFiles)
    {
        $commandTester = $this->getExecutedCommandTester('wame:generate:entity', $options);

        $this->assertContains('Everything is OK!', $commandTester->getDisplay());

        foreach ($compareFiles as $generatedFile) {
            $this->assertFileEqualsTestFile($generatedFile);
        }
    }

    public function getCommandData()
    {
        return [
            //Generate SimpleBook using the traditional syntax in fields option
            [
                [
                    'entity' => 'WameGeneratorBundle:SimpleBook',
                    '--fields' => "title:string(unique=true display=true validation=NotBlank)"
                ],
                ['Entity/SimpleBook.php', 'Repository/SimpleBookRepository.php']
            ],
            //Generate SimpleBook using json and without specifying bundle
            [
                [
                    'entity' => 'SimpleBook',
                    '--fields' => '{"title":{"type":"string","unique":true,"display":true,"validation":["NotBlank"]}}'
                ],
                ['Entity/SimpleBook.php', 'Repository/SimpleBookRepository.php']
            ],
            //Let's now create a more full setup with Book, Library, Author, BookDetailInfo and Person,
            [EntityBook::$commandOptions, ['Entity/Book.php']],
            [EntityBookDetailInfo::$commandOptions, ['Entity/BookDetailInfo.php']],
            [EntityLibrary::$commandOptions, ['Entity/Library.php']],
            [EntityPerson::$commandOptions, ['Entity/Person.php']],
            [EntityAuthor::$commandOptions, ['Entity/Author.php']],
            //Test usage of subdirectory
            [EntityAdminSpecialConfiguration::$commandOptions, ['Entity/Admin/SpecialConfiguration.php']],
        ];
    }

    public function testInteractiveCommandResult()
    {
        $commandTester = $this->getExecutedCommandTester('wame:generate:entity', [],
            //Entity name:
            "Interacted\n"
            //Add default behaviours (timestampable, blameable, softdeleteable) [yes]
            ."no\n"
            //If 'no' is provided, then the behviour questions will be asked
            //Add timestampable behaviour [yes]
            ."no\n"
            //Add blameable behaviour [yes]
            ."no\n"
            //Add softdeleteable behaviour [yes]
            ."no\n"
            //New field name (press <return> to stop adding fields)
            ."title\n"
            //Field type [string]
            ."string\n"
            //Field length [255]
            ."100\n"
            //Is nullable [false]
            ."false\n"
            //unique [false]
            ."false\n"
            //Add validation (press <return> to stop adding):
            ."Length\n"
            //Provide max for length
            ."100\n"
            //Provide min for length
            ."10\n"
            //Add validation (press <return> to stop adding):
            ."\n"
            //New field name (press <return> to stop adding fields)
            ."\n"
            //Which field you want to use? (leave empty to skip) [title]
            ."title"
        );
        $commandDisplay = $commandTester->getDisplay();
        $this->assertContains('Entity name:', $commandDisplay);
        $this->assertContains('Add timestampable behaviour', $commandDisplay);
        $this->assertContains('Add blameable behaviour', $commandDisplay);
        $this->assertContains('Add softdeleteable behaviour', $commandDisplay);
        $this->assertContains('o2o: one2one, m2o: many2one, m2m: many2many, o2m: one2many, e: enum', $commandDisplay);
        $this->assertContains('Field type [string]', $commandDisplay);
        $this->assertContains('Field length [255]', $commandDisplay);
        $this->assertContains('Is nullable [false]', $commandDisplay);
        $this->assertContains('Unique [false]', $commandDisplay);
        //With the used settings, NotBlank should be automatically set
        $this->assertContains('These constraints are already set:', $commandDisplay);
        $this->assertContains('NotBlank', $commandDisplay);
        //The NotBlank option should not be shown if it's already automatically set.
        $this->assertNotContains('nb: NotBlank', $commandDisplay);
        $this->assertNotContains('Provide max for length', $commandDisplay);
        $this->assertNotContains('Provide min for length', $commandDisplay);
        $this->assertContains('Current validations: NotBlank, Length', $commandDisplay);
        $this->assertContains('Available fields: 1: title', $commandDisplay);
        $this->assertContains('Everything is OK!', $commandDisplay);

        $this->assertFileEqualsTestFile('Entity/Interacted.php');
    }
}