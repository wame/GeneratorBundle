<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityBook
{
    public static $commandOptions = [
        'entity' => 'Book',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "
title:
    type: string
    unique
    display
    validation:
        NotBlank
stock:
    type: int
    validation: 
        NotNull
        GreaterThanOrEqual:
            value: 0
borrowers:
    type: one2many
    targetEntity: Person
    orphanRemoval:
    mappedBy: book
    validation:
        Expression:
            value: \"value.count() > this.getStock()\"
            message: \"A book cannot be borrowed more often than it exists in stock\"
author:
    type: many2one
    targetEntity: Author
    validation:
        NotNull
        Valid
libraries:
    type: many2many
    targetEntity: Library
    mappedBy: books
bookDetailInfo:
    type: one2one
    targetEntity: BookDetailInfo
    mappedBy: book
    validation:
        Valid
"
    ];
}