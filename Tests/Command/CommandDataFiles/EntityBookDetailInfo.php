<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityBookDetailInfo
{
    public static $commandOptions = [
        'entity' => 'BookDetailInfo',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "{
            book: {
                type: one2one,
                targetEntity: Book,
                inversedBy: bookDetailInfo,
                validation: {
                    NotNull
                }
            },
            type: {
                type: enum,
                enumType: BookType,
                nullable,
            },
            subTitle: {
                type: string,
                nullable,
            },
            summary: {
                type: text,
                nullable,
            },
            inLibrarySince: {
                type: date,
                validation: {
                    NotNull,
                    Date,
                }
            }
        }"
    ];
}