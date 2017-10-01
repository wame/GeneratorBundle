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
}