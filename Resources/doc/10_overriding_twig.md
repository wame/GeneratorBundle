WameGeneratorBundle
=====================

1.  [Introduction](1_introduction.md)
2.  [Getting started](2_getting_started.md)
3.  [Configuration options](3_configuration.md)
4.  [Entity Generation](4_entity_generation.md)
5.  [CRUD Generation](5_crud_generation.md)
6.  [Enum Generation](6_enum_generation.md)
7.  [Form Generation](7_form_generation.md)
8.  [Voter Generation](8_voter_generation.md)
9.  [Datatable Generation](9_datatable_generation.md)
10. **Overriding twig files**
11. [Extending this bundle](11_extending_bundle.md)


## Overriding twig files

Just like the SensioGenerator, you can overwrite the twig skeleton files of the WameGenerator
in the following directory:
`App/Resources/WameGeneratorBundle/skeleton`

More than just crud, this bundle allows you to overwrite the following parts as well:
- entity
- form
- repository
- voter
- translation

For more information about overwriting skeleton files, see: http://symfony.com/doc/3.0/bundles/SensioGeneratorBundle/index.html#overriding-skeleton-templates
