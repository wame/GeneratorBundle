<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityPerson
{
    public static $commandOptions = [
        'entity' => 'Person',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "
firstName:
    type: string
    display
    validation:
        NotBlank
lastName:
    type: string
    validation:
        NotBlank
email:
    type: string
    nullable
    validation:
        Email
book:
    type: many2one
    targetEntity: Book
    inversedBy: borrowers
    nullable
"
    ];
}