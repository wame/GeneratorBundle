WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| 3.  [Configuration options](3_configuration.md#wamegeneratorbundle)
| 4.  [Entity Generation](4_entity_generation.md#wamegeneratorbundle)
| 5.  [CRUD Generation](5_crud_generation.md#wamegeneratorbundle)
| 6.  [Enum Generation](6_enum_generation.md#wamegeneratorbundle)
| **7.  Form Generation**
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)


## Form generation

command: `wame:generate:form` 

For an entity a form can be generated either during CRUD generation or
if you wish to generate the form only, you can use this form-command.

### Argument

You can directly specify the entity class name in the argument. 

    php bin/console wame:generate:form Product
    
If you need to generate the form for an entity in a different bundle than the default
bundle, you can use the shortcut notation:

    php bin/console wame:generate:form AcmeBundle:Product

### Options

#### `--overwrite`  
Add this option if you want to overwrite the file if it already exists.