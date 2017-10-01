WameGeneratorBundle
=====================

| 1.  [Introduction](1_introduction.md)
| 2.  [Getting started](2_getting_started.md)
| 3.  [Configuration options](3_configuration.md)
| 4.  [Entity Generation](4_entity_generation.md)
| 5.  [CRUD Generation](5_crud_generation.md)
| 6.  [Enum Generation](6_enum_generation.md)
| 7.  [Form Generation](7_form_generation.md)
| 8.  [Voter Generation](8_voter_generation.md)
| **9.  Datatable Generation**
| 10. [Overriding twig files](10_overriding_twig.md)
| 11. [Extending this bundle](11_extending_bundle.md)


## Datatable generation

command: `wame:generate:datatable` 

For an entity a datatable can be generated either during CRUD generation or
if you wish to generate a datatable class only, you can use this form-command.

The generator will add the datatable class, but also
add the abstract classes 'AppDatatable' and 'DatatableResultService' 
if they do not exist already. 
This AppDatatable will be extended by generated datatables. 

The DatatableResultService is a service class that will be used in controllers. It will call for
a modifyquery callback which you can set in your datatables to change
queries to your specific needs. This way you do not need to mess with code
inside your controllers.


### Argument

You can directly specify the entity class name in the argument. 

    php bin/console wame:generate:datatable Product
    
If you need to generate the datatable for an entity in a different bundle than the default
bundle, you can use the shortcut notation:

    php bin/console wame:generate:voter AcmeBundle:Product

### Options

#### `--overwrite`  
Add this option if you want to overwrite the file if it already exists.

This overwrite option won't affect changes you've made to the 
AppDatatable and DatatableResultService.