WameGeneratorBundle
=====================

1.  [Introduction](1_introduction.md)
2.  [Getting started](2_getting_started.md)
3.  [Configuration options](3_configuration.md)
4.  [Entity Generation](4_entity_generation.md)
5.  [CRUD Generation](5_crud_generation.md)
6.  [Enum Generation](6_enum_generation.md)
7.  [Form Generation](7_form_generation.md)
8.  **Voter Generation**
9.  [Datatable Generation](9_datatable_generation.md)
10. [Overriding twig files](10_overriding_twig.md)
11. [Extending this bundle](11_extending_bundle.md)


## Voter generation

command: `wame:generate:voter` 

For an entity a voter can be generated either during CRUD generation or
if you wish to generate a voter class only, you can use this form-command.

The generated voter will always contain attributes for actions that could 
 be generated. Each attribute is prefixed with the upper cased underscored entity name. 

For example, a voter for the entity ProductStore will get the following 
attributes:  
PRODUCT_STORE_INDEX, PRODUCT_STORE_SEARCH, PRODUCT_STORE_VIEW,
PRODUCT_STORE_CREATE, PRODUCT_STORE_EDIT, PRODUCT_STORE_DELETE

By default all attributes will only return true for the admin, so you'll most
likely need to change this file.

Additionally, the generator will add the abstract class 'AppVoter' if it doesn't
exist already. This AppVoter will be extended by generated voters. 

### Argument

You can directly specify the entity class name in the argument. 

    php bin/console wame:generate:voter Product
    
If you need to generate the voter for an entity in a different bundle than the default
bundle, you can use the shortcut notation:

    php bin/console wame:generate:voter AcmeBundle:Product

### Options

#### `--overwrite`  
Add this option if you want to overwrite the file if it already exists.

This overwrite option won't affect changes you've made to the 
AppVoter.

### TIP

If you haven't set up any security settings yet, like configuring the FOSUSerBundle,
the generated code becomes hard to check in your interface as you need to be the admin. 

A quick way to log in, without going though the process of setup up a full
 security setup first, 
is to add the following to your `app/config/config_dev.yml`

    security:
        encoders:
            Symfony\Component\Security\Core\User\User: plaintext
        firewalls:
            main:
                anonymous: ~
                http_basic: ~
        providers:
            in_memory:
                memory:
                    users:
                        admin:
                            password: admin
                            roles: 'ROLE_ADMIN'

This will directly enable you to login using basic_auth when you are in development-environment.
