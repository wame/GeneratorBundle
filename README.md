WameGeneratorBundle
=====================

This bundle is heaviliy altered version of the
[SensioGeneratorBundle](http://symfony.com/doc/3.0/bundles/SensioGeneratorBundle/index.html).
It specifically has many changes and extra features
regarding entity and CRUD generations.


## Documentation

1. [Introduction](Resources/doc/introduction.md)
2. [Getting started](Resources/doc/getting_started.md)
3. [Configuration options](Resources/doc/configuration.md)
4. [Entity Generation](Resources/doc/entity_generation.md)
5. [CRUD Generation](Resources/doc/crud_generation.md)
6. [Enum Generation](Resources/doc/enum_generation.md)
7. [Form Generation](Resources/doc/form_generation.md)
8. [Voter Generation](Resources/doc/voter_generation.md)
9. [Datatable Generation](Resources/doc/datatable_generation.md)
10. [Overriding twig files](Resources/doc/overriding_twig.md)
11. [Extending this bundle](Resources/doc/extending_bundle.md)


#### TODO's

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
    - lots of tests need still to be added, like interactive tests
     and testing expected failures.
