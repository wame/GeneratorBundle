WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| 3.  [Configuration options](3_configuration.md#wamegeneratorbundle)
| 4.  [Entity Generation](4_entity_generation.md#wamegeneratorbundle)
| **5.  CRUD Generation**
| 6.  [Enum Generation](6_enum_generation.md#wamegeneratorbundle)
| 7.  [Form Generation](7_form_generation.md#wamegeneratorbundle)
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)


## CRUD generation

command: `wame:generate:crud` 

Similar to [doctrine:generate:crud](https://symfony.com/doc/3.0/bundles/SensioGeneratorBundle/commands/generate_doctrine_crud.html)
 command, but it will also allow you to generate a voter and datatable.

### Argument

You can directly specify for which entity you want to generate CRUD:

    php bin/console wame:generate:crud Product
    
If you only specify an entity name, like the example above, the generator assumes that
you're using the default bundle, which you can specify in the
[configuration](3_configuration.md#wamegeneratorbundle).

If the entity is located in a different bundle, you can still use the
shortcut notation:

    php bin/console wame:generate:entity AcmeBundle:Product


### Options

#### `--entity` (removed)  
Sensio already marked this option as deprecated and it will be removed in
Symfony 4. Since it can only cause confusion, this option has been
left out for this generator.

#### `--format` (removed)
The annotation format is the only supported format in this generator.
 
#### `--with-datatable`  
Add this option if you want to usage a datatable class instead of a 
plain table. 
Using this option only makes sense in non-interactive mode as it will be
set true in interactive mode.
#### `--with-voter`  
Add this option if you want to have the voter generated.
Using this option only makes sense in non-interactive mode as it will be
set true in interactive mode.

#### `--route-prefix`

The prefix to use for each route that identifies an action.


#### `--with-write` (*allowed values:* `yes`|`no` *default:* `yes`)

Whether or not to generate the new, create, edit, update and delete actions.

#### `--overwrite`  
Add this option if you want to overwrite files if they already exist. 
Be cautious of using this option as you may lose custom changes.
