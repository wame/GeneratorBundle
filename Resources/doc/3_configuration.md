WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| **3.  Configuration options**
| 4.  [Entity Generation](4_entity_generation.md#wamegeneratorbundle)
| 5.  [CRUD Generation](5_crud_generation.md#wamegeneratorbundle)
| 6.  [Enum Generation](6_enum_generation.md#wamegeneratorbundle)
| 7.  [Form Generation](7_form_generation.md#wamegeneratorbundle)
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)

## Configuration options
No configuration is required, but you might want to alter some
settings to specific needs. 
The following configuration show the default settings:

    wame_generator:
        default_bundle: 'AppBundle' #The bundle used whenever none is specified.
        enable_voters: true         #use false if you don't plan on using voters
        enable_traits: true         #use false if you don't plan on using gedmo traits
        enable_datatables: true     #use false if you don't plan on using SgDatatables

 