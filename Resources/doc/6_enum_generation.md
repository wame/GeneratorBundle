WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| 3.  [Configuration options](3_configuration.md#wamegeneratorbundle)
| 4.  [Entity Generation](4_entity_generation.md#wamegeneratorbundle)
| 5.  [CRUD Generation](5_crud_generation.md#wamegeneratorbundle)
| **6.  Enum Generation**
| 7.  [Form Generation](7_form_generation.md#wamegeneratorbundle)
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)


## Enum generation

command: `wame:generate:enum` 

Using the [DoctrineEnumBundle](https://github.com/fre5h/DoctrineEnumBundle), you
can add enum type classes which you can use as types for your entities. 

### Argument

You can directly specify the enum type class name in the argument. 

    php bin/console wame:generate:enum ProductType
    
If you need to generate the enum in a different bundle than the default
bundle, you can use the shortcut notation:

    php bin/console wame:generate:enum AcmeBundle:ProductType

### Options

#### `--options`  

With the --options option (apologies for confusing name) 
a string can be provided that contains sets for each enum-option
in the following format:  
"value,CONST,Label|value-two,CONST_TWO,Label two"

For example, if you wish to create a StatusType with 'new', 'in-progress' and
'completed' options, you could use the following command:  

    php bin/console wame:generate:enum StatusType --options="new,NEW,New|in-progress,IN_PROGRESS,In progress|completed,COMPLETED,Completed"

If you so prefer, you can also use an array format:

    php bin/console wame:generate:enum StatusType --options="[
        [new,NEW,New],
        [in-progress,IN_PROGRESS,In progress]
        [completed,COMPLETED,Completed]
    ]"

If you're feeling extra lazy, you can leave out the constant and label. These 
will then automatically be determined by the generator.

    php bin/console wame:generate:enum StatusType --options="new|in-progress|completed"


#### `--overwrite`  
Add this option if you want to overwrite files if they already exist.