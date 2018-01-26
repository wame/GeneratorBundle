WameGeneratorBundle
=====================

This bundle is a heaviliy altered version of the
[SensioGeneratorBundle](http://symfony.com/doc/3.0/bundles/SensioGeneratorBundle/index.html).
It specifically has changes and extra features regarding entity and CRUD generations.


## Documentation

1. [Introduction](Resources/doc/1_introduction.md#wamegeneratorbundle)
2. [Getting started](Resources/doc/2_getting_started.md#wamegeneratorbundle)
3. [Configuration options](Resources/doc/3_configuration.md#wamegeneratorbundle)
4. [Entity Generation](Resources/doc/4_entity_generation.md#wamegeneratorbundle)
5. [CRUD Generation](Resources/doc/5_crud_generation.md#wamegeneratorbundle)
6. [Enum Generation](Resources/doc/6_enum_generation.md#wamegeneratorbundle)
7. [Form Generation](Resources/doc/7_form_generation.md#wamegeneratorbundle)
8. [Voter Generation](Resources/doc/8_voter_generation.md#wamegeneratorbundle)
9. [Datatable Generation](Resources/doc/9_datatable_generation.md#wamegeneratorbundle)
10. [Overriding twig files](Resources/doc/10_overriding_twig.md#wamegeneratorbundle)
11. [Extending this bundle](Resources/doc/11_extending_bundle.md#wamegeneratorbundle)


#### TODO's

- **Adaptions for Symfony4**
- Usage of the 'Resources/translations/roles.(en|nl).yml.twig'
- Configuration: 
    - There are configuration settings for using different trait-classes,
but the generator does not take these settings into account.
This should be implemented or the settings should be removed.
    - A setting for using datatables by default exists, but the generator
    currently ignores this setting. This is still to be implemented.
    - More settings/defaults:  
    we may want to set specific traits to be used or not by default. 
    For instance, some application may never use datatables, so that
    option should be possible to disable for those applications.
- Tests:
    - lots of tests still need to be added, like interactive tests
     and testing expected failures.
     -  **Doctrine PSR-4 bug:**  
The  [getBasePathForClass](https://github.com/doctrine/DoctrineBundle/blob/1.7.1/Mapping/DisconnectedMetadataFactory.php#L151) 
method in 
[DisconnectedMetadataFactory](https://github.com/doctrine/DoctrineBundle/blob/1.7.1/Mapping/DisconnectedMetadataFactory.php)
checks entities by their namespace as if PSR-0 is used. Without this
GeneratorBundle being in directory 'wame', the 
[RuntimeException](https://github.com/doctrine/DoctrineBundle/blob/1.6.12/Mapping/DisconnectedMetadataFactory.php#L158) 
will be thrown.
