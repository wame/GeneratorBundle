<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityAuthor
{
    public static $commandOptions = [
        'entity' => 'Author',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "{
            firstName : {
                type: string,
                display,
                validation: {
                    NotBlank
                },
            }, 
            lastName : {
                type: string,
                validation: {
                    NotBlank
                },
            },
            email: {
                type: string,
                nullable,
                validation: {
                    Email
                },
            },
            books: {
                type: one2many,
                targetEntity: Book,
                mappedBy: author,
            }
        }"
    ];
}