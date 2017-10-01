WameGeneratorBundle
=====================


| **1.  Introduction**
| 2.  [Getting started](2_getting_started.md)
| 3.  [Configuration options](3_configuration.md)
| 4.  [Entity Generation](4_entity_generation.md)
| 5.  [CRUD Generation](5_crud_generation.md)
| 6.  [Enum Generation](6_enum_generation.md)
| 7.  [Form Generation](7_form_generation.md)
| 8.  [Voter Generation](8_voter_generation.md)
| 9.  [Datatable Generation](9_datatable_generation.md)
| 10. [Overriding twig files](10_overriding_twig.md)
| 11. [Extending this bundle](11_extending_bundle.md)


## Introduction

This bundle is a generator like the [SensioGeneratorBundle](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html)
, with changes to fit
specific needs:

* Conform PHP 7.1 typehints
* Other options for entity generation:
    * Usage of enums (by using [DoctrineEnumBundle](https://github.com/fre5h/DoctrineEnumBundle))
    * Usage of [StofDoctrineExtensionsBundle](http://symfony.com/doc/master/bundles/StofDoctrineExtensionsBundle/index.html): timestampable, softdeleteable, blameable
    * Usage of translation-file
    * Usage of validations
* Other options for CRUD generation:
    * Usage of [DatatablesBundle](https://github.com/stwe/DatatablesBundle)
    * Usage of Voters
* Ability to override many more aspects of this bundle


Basic usage is quite similar, but 'doctrine' is replaced with 'wame' in the
command names: `doctrine:generate:entity` becomes `wame:generate:entity`.
Furthermore, not all doctrine-commands are replaced by this bundle and some
entirely different commands have been added.

In the table below you can see a quick overview of the commands compared to Sensio:



| Sensio                     | WAME                    |
|----------------------------|-------------------------|
| generate:bundle            | *n/a*                   |
| generate:doctrine:entities | *n/a*                   |
| generate:controller        | *n/a*                   |
| datatable:generate:class   | *n/a*                   |
| generate:doctrine:entity   | wame:generate:entity    |
| generate:doctrine:crud     | wame:generate:crud      |
| generate:doctrine:form     | wame:generate:form      |
| *n/a*                      | wame:generate:voter     |
| *n/a*                      | wame:generate:datatable |
| *n/a*                      | wame:generate:enum      |