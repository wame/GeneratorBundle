<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Tests\Command\CommandDataFiles;

class EntityAdminSpecialConfiguration
{
    public static $commandOptions = [
        'entity' => 'Admin\SpecialConfiguration',
        '--behaviours' => ['blameable', 'timestampable', 'softdeleteable'],
        '--fields' => "
title:
    type: string
    display
    validation:
        NotBlank
"
    ];
}