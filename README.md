### under construction
This readme isn't finished yet.

WameSensioGeneratorBundle
=====================

Extension and modification of the `SensioGeneratorBundle`. 
Basic usage is near identical, except that `doctrine` is replaced with `wame` in the
command name. E.g. `doctrine:generate:entity` becomes `wame:generate:entity`

Differences are further explained below.

For more information about the sensioGeneratorBundle, see the official
[SensioGeneratorBundle documentation](http://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html).


##Entity generation

command `wame:generate:entity`  
usage: `php bin/console wame:generate:entity AppBundle:EntityName`

* The entity-argument is required, unlike sensio (which will be required in later release)

#### Wame-specific options:
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

    `php bin/console wame:generate:entity AppBundle:Product --fields="
    bookStore:many2one(targetEntity=BookStore)
    type:enum(enumType=BookType)
    "` -n

#### Validations

In interactive mode, for each field you'll be asked to add validations.

You can also pass validations to the fields in non-interactive mode, for example:

    `php bin/console wame:generate:entity AppBundle:Customer --fields="
    email:string(validation=Email;NotBlank)
    "` -n

Multiple validations can be passed by seperating them by a ';' as shown in the example.

It is not possible to set validation-options. You'll still need to modify the entity if
you need them.
