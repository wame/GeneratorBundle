<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityLibrary
{
    public static $commandOptions = [
        'entity' => 'Library',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "{
            name : {
                type: string,
                display,
                unique
                validation: {
                    NotBlank
                },
            },
            books: {
                type: many2many,
                targetEntity: Book,
                inversedBy: libraries,
            }
        }"
    ];
}