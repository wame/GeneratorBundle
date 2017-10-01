WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| 3.  [Configuration options](3_configuration.md#wamegeneratorbundle)
| 4.  [Entity Generation](4_entity_generation.md#wamegeneratorbundle)
| 5.  [CRUD Generation](5_crud_generation.md#wamegeneratorbundle)
| 6.  [Enum Generation](6_enum_generation.md#wamegeneratorbundle)
| 7.  [Form Generation](7_form_generation.md#wamegeneratorbundle)
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| **10. Overriding twig files**
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)


## Overriding twig files

Just like the SensioGenerator, you can overwrite the twig skeleton files of the WameGenerator
in the following directory:
`App/Resources/WameGeneratorBundle/skeleton`

More than just crud, this bundle allows you to overwrite any part:
- crud
- datatable
- entity
- enum
- form
- repository
- security
- translations

For more information about overwriting skeleton files, see: http://symfony.com/doc/3.0/bundles/SensioGeneratorBundle/index.html#overriding-skeleton-templates
