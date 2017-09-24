WameGeneratorBundle
=====================

Modified version of the `SensioGeneratorBundle`. 
Basic usage is quite similar, except that 'doctrine' is replaced with 'wame' in the
command names: `doctrine:generate:entity` becomes `wame:generate:entity`

For more information about the sensioGeneratorBundle, see the official
[SensioGeneratorBundle documentation](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html).

The SensioGenerator lacks what we want:
* PHP 7.1 enables more type hinting. We intent to use them.
* Generation of entity relationships (many2one, one2many, one2one, many2many)
* Ability to use enum-types.
* Gedmo traits: we often use softdeleteable, timestampable and/or blameable.
* Datatables: for large sets of data a simple table won't do, so we use sg_datatables for them.
* Voters: for many entities with CRUD, we use specific voters. 
* Translations: even though we may not need a multilanguage application, 
we use translation files for property names to keep their definition centralized.

This bundle adds these features.

## Installation

    composer require wame/generator-bundle:dev-master --dev
    
In your `app/AppKernel.php` file, add the bundle for the development environment:

     public function registerBundles()
        {
            ...
            
            if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
                ...
                if ('dev' === $this->getEnvironment()) {
                    $bundles[] = new \Wame\GeneratorBundle\WameGeneratorBundle();
                }
            }
            ...
        }

 
## Configuration options
No configuration is required, but you might want to alter some
settings to specific needs. 
The following configuration show the default settings:

    wame_generator:
        default_bundle: 'AppBundle' #The bundle used whenever none is specified.
        enable_voters: true         #use false if you don't plan on using voters
        enable_traits: true         #use false if you don't plan on using gedmo traits
        enable_datatables: true     #use false if you don't plan on using SgDatatables


## Required / Recommended bundles

By default, the following bundles are assumed to be installed and configured:
- [DatatablesBundle](https://github.com/stwe/DatatablesBundle)
for datatables.  
If you do not wish to use datatables, you could add 
`wame_generator.enable_datatables: false` to your config.yml, so
that you won't be bothered with the question of using datatables.
- [StofDoctrineExtensionsBundle](http://symfony.com/doc/master/bundles/StofDoctrineExtensionsBundle/index.html)
for Gedmo trait-options. 
If you do not wish to use these traits, you could add
`wame_generator.enable_traits: false` to your config.yml, so
that you won't be bothered with questions about using traits.
- [DoctrineEnumBundle](https://github.com/fre5h/DoctrineEnumBundle)
 for enumerables.  
 If you do not intent to use enum, simply do not use it when asked for a
 property type.
 

## Entity generation

command: `wame:generate:entity`  

Similar to doctrine:generate:entity command, but with changes described below.

#### Removed options:
Compared to doctrine's entity generator, some options are not available in this bundle:
* --entity  
Since there's already an argument 'entity' it'll also be removed in symfony 4, 
this option is left out for this generator. 
* --format  
The 'annotation' format is the only format in this bundle.
The entity generator is rendered through twig files. Support for multiple formats
 hasn't been implemented. 

#### Additional options:
* --no-blameable  
Set this option to ensure blameable isn't used.
* --no-timestampable  
Set this option to ensure timestampable isn't used.
* --no-softdeleteable  
Set this option to ensure softdeleteable isn't used.
* --behaviours  
Use to define one of the behaviors 'softdeleteable', 'timestampable' or 'blameable'.
Can be used multiple times for adding multiple behaviours.  
eg `--behaviours=timestampable --behaviours=blameable`
* --display-field  
define which property will be used in the `__toString` method.  
eg `--display-field=title`
* --no-validation  
Set this option to skip questions about validation


#### Altered --fields option

The `--fields` option can be used in the exact same way as in the original doctrine generator:

    wame:generate:entity Product --fields="title:string(255)"
    
Once there are quite a few fields with several settings each fields, this syntax can
become a mess. Also, there are limitations to using this syntax 
(more about this will be explained in the 'Validations' section).

To provide a cleaner way of adding fields, the ability to use json-like syntax has been added.
The above example would become:

    wame:generate:entity Product --fields="{title:{type: string, length: 255}}"

Json requires that keys and values are surrounded by quotes. Since the
fields-option requires a string, all json-quotes would have to be escaped.
You can use escape quotes if you prefer, but once the fields-option is parsed,
quotes will be added automatically, so you don't need to.

Additionally, you can parse boolean options, without value. For example,
`{title:{type:string,nullable}}` would be the same as 
`{title:{type:string,nullable:true}}`.

Furthermore, newlines, tabs and abundant spaces are automatically converted,
so if your command-line can handle it, you could write the example as below:

    wame:generate:entity Product --fields="{
        title:{
            type: string,
            length: 255
        }
    }"

#### Default bundle (Appbundle)
After dozens of times, even typing a simple AppBundle:EntityName becomes cumbersome. 
Since many application will only use the AppBundle, the generator assumes
 this bundle should be used whenever no bundle is provided.
So instead of `wame:generate:entity AppBundle:Product` you can 
use `wame:generate:entity Product`. 

If you wish to use a different default bundle, you can override this
setting in your config.yml

    wame_generator:
        default_bundle: AppBundle
        
You can replace AppBundle with any bundle name you need it to be.

#### Additional types

In interactive mode, you can add additional types for relations and enums:
* many2one
* one2many
* one2one
* many2many
* enum

If you whish to pass these types as fields without interaction, you'll have to
specifiy the targetEntity for relations and the enumType for enum.  
Example:

    `php bin/console wame:generate:entity Product --fields="
        bookStore:many2one(targetEntity=BookStore)
        type:enum(enumType=BookType)
    "` -n

#### Validations

In interactive mode, for each field you'll be asked to add validations.

Certain validations will be added automatically. For example: if a string-field is
not-nullable, then the 'NotBlank' validation will be applied. Other not-nullable
fields will get the 'NotNull' validation.

If you don't want to be bothered with validation, you can use the
`--no-validation` option. This will also prevent automatically added validations.

You can also pass validations to the fields in non-interactive mode, for example:

    `php bin/console wame:generate:entity Customer --fields="
        email:string(validation=Email;NotBlank)
    "` -n

Multiple validations can be passed by seperating them by a ';' as shown in the example.

In non-interactive mode no validations will be added automatically, so make sure you 
add all desired validations yourself.

It is not possible to set validation-options if you want to use the traditional
field syntax. If you want to add validation-options, you can use the json-syntax instead:

    php bin/console wame:generate:entity Customer --fields="{
        email:{
            type:string,
            validation:{
                Email,
                NotBlank,
                Length: {
                    min: 5,
                    max: 150,
                    minMessage: \"We do not believe an email can have fewer than 5 characters.\",
                    maxMessage: \"For some inexplicable emails longer than 150 chars are not allowed here.\"
                }
            }
        }
    }"


#### Savepoint

Ever generated an entity, made a mistake after you've already added several fields and out
of reflex you canceled the command? Savepoint will prevent you from starting all over.


In interactive mode, after every field you've added, a savepoint-file will be updated.
If during the next field you make a mistake, you can cancel the generation and
start from the savepoint by using the savepoint option, eg:

    php bin/console wame:generate:entity --savepoint


## CRUD generation

command: `wame:generate:crud` 

Similar to doctrine:generate:crud command, but with changes described below.

#### Removed options:
For the exact same reasons as the entity generator, the `--entity`
and `--format` options have been removed.

#### Additional options
* --with-datatable  
add this option if you want to have the datatable generated. 
* ---with-voter
add this option if you want to have the voter generated.

## Form generation

For an entity a form can be generated either during CRUD generation or
if you wish to generate the form only, you can use the specific form-command.

command: `wame:generate:form`  
argument: `entity`  
options:  `--overwrite`

The generated form will contain all entity-fields with the exception of the id-field and
the fields that are defined in the gedmo-traits.

## Datatable generation

For an entity a datatable can be generated either during CRUD generation or
if you only wish to generate the datatable, you can use the specific command for
datatable generation.

command: `wame:generate:datatable`  
argument: `entity`  
options:  `--overwrite`

The datatable that is generated will contain all fields, except relationships
and fields that are defined in the traits. 
Most likely you'll need to make some changes to this file to fit your needs.

If you generate the first datatable, two extra classes will be generated:
- AppDatatable`   
This is an abstract class that other datatables will extend.
- DatatableResultService  
This is a service class that will be used in controllers. It will call for
a modifyquery callback which you can set in your datatables to change
queries to your specific needs. This way you do not need to mess with code
inside your controllers.

You can change these files to your needs, but make sure you won't break the
code that is being generated for controllers.

The generator will generate the files for these classes only if they do not
exist yet. The `--overwrite` option has no effect on these files.


## Voter generation

For an entity a voter can be generated either during CRUD generation or
if you only wish to generate the voter, you can use the specific voter-command.

command: `wame:generate:voter`  
argument: `entity`  
options:  `--overwrite`

The generated voter will always contain attributes for action that could 
possibily be generated, no matter if they are actually generated or not:

* INDEX  
intended for the index action
* SEARCH  
intended for the results action (which is generated when you're using datatables)
* VIEW  
intended for the show action
* CREATE  
intended for the new action
* EDIT  
intended for the edit action
* DELETE  
intended for the delete action

Each attribute is prefixed with the upper cased underscored entity name. Eg a voter for the entity
ProductStore would have an attribute PRODUCT_STORE_INDEX.

By default all attributes will only return true for the admin, so you'll most
likely need to change this file.

When you generate the first voter an additional class will be generated:
- AppVoter  
This class is extended by the other voters that are generated. You can
alter this file to fit your needs. 
This file will only be generated if it doesn't exist yet and is not affected
by the `--overwrite` option.


## Enum generation

command: `wame:generate:enum`  
argument: `enum`  
options:  `--overwrite`, `--options`

For `--options` a string can be provided that contains sets for each options
in the following format:  
"value,CONST,Label|value-two,CONST_TWO,Label two"

For example, if you wish to create a StatusType with 'new', 'in-progress' and
'completed' options, you could use the following command:  

    php bin/console wame:generate:enum StatusType --options="new,NEW,New|in-progress,IN_PROGRESS,In progress|completed,COMPLETED,Completed"

If you so prefer, you can also use the array format, but make sure it is provided as a string:

    php bin/console wame:generate:enum StatusType --options="[
        [new,NEW,New],
        [in-progress,IN_PROGRESS,In progress]
        [completed,COMPLETED,Completed]
    ]"

If you're feeling extra lazy, you can leave out the constant and label. These 
will then automatically be determined by the generator.

    php bin/console wame:generate:enum StatusType --options="new|in-progress|completed"

## Overwriting twig files

Just like the SensioGenerator, you can overwrite the twig skeleton files of the WameGenerator
in the following directory:
`App/Resources/WameGeneratorBundle/skeleton`

For more information about overwriting skeleton files, see: http://symfony.com/doc/2.5/bundles/SensioGeneratorBundle/index.html#overriding-skeleton-templates

More than just crud, this bundle allows you to overwrite the following parts as well:
- entity
- form
- repository
- voter
- translation

## TODO's

- Usage of the 'Resources/translations/roles.(en|nl).yml.twig'
- Interface: a concept is created, but no longer works after several changes.
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
- Tests  
    - Currently, this bundle still holds the sensio-testfiles, but these no
    longer are compatible with this bundle. These tests need to be modified. 
    - The sensiogenerator also generated test-files. Since those files are rather empty,
    they are left out, but generating test files still might be quite helpful.
