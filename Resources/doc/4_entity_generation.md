WameGeneratorBundle
=====================

1\.  [Introduction](1_introduction.md#wamegeneratorbundle)
| 2.  [Getting started](2_getting_started.md#wamegeneratorbundle)
| 3.  [Configuration options](3_configuration.md#wamegeneratorbundle)
| **4.  Entity Generation**
| 5.  [CRUD Generation](5_crud_generation.md#wamegeneratorbundle)
| 6.  [Enum Generation](6_enum_generation.md#wamegeneratorbundle)
| 7.  [Form Generation](7_form_generation.md#wamegeneratorbundle)
| 8.  [Voter Generation](8_voter_generation.md#wamegeneratorbundle)
| 9.  [Datatable Generation](9_datatable_generation.md#wamegeneratorbundle)
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| 11. [Extending this bundle](11_extending_bundle.md#wamegeneratorbundle)


## Entity generation

command: `wame:generate:entity`

### Interactive mode

You do not need to specify any argument or option if you're using interactive
mode. 

The interactive mode is similar to doctrine's entity generation, but with 
extra features:

- **behaviours**  
    You can specify whether or not to include the blameable, softdeleteable and/or 
    timestampable traits. 
- **Relationship types**  
    Just like string, date, integer and decimal, the generator allows you to
    specify `many2one`, `one2many`, `one2one` and `many2many` types.
    After choosing any of these relation-types, the generation will ask you
    relevant questions such as the related entity.
- **Enum types**  
    If you're using enumerables, the generator allows you to specify these
    as field-types.
- **Validations**  
    It's good practise to use validations, but these are sometimes easily 
    forgotten. The generator will help you by providing you options to
    directly choose validations.        
    Moreover, the generator will preselect validations based on the time you've
    chosen and automatically apply validations `NotBlank`, `NotNull`, `Valid`
    depending on you're previously made choises.
-  **Display field** (__toString)  
    It's a nice-to-have to just ouput your entity without calling methods
    like `->getTitle()` first. For that `__toString()` must be set and
    the generator will help you with this by allowing you to specify
    which field you want to use for this method.     
    Easy to regocnize fields, such as title or anything with 'name' in it will
    automatically suggested.

### Argument

Optionally, you can directly specify which entity you want to create using
the first (and only) argument. 

For instance, if you want to create a Product entity, you can use

    php bin/console wame:generate:entity Product
    
Note that unlike the doctrine-command, you are not required to use a shortcut
notation. If you only specify an entity name, the generator assumes that
you're using the default bundle, which you can specify in the
[configuration](3_configuration.md#wamegeneratorbundle).

If you want, you can still use the shortcut notation to specify a different bundle

    php bin/console wame:generate:entity AcmeBundle:Product


### Options


#### `--entity` (removed)
Sensio already marked this option as deprecated and it will be removed in
Symfony 4. Since it can only cause confusion, this option has been
left out for this generator.

#### `--format` (removed)

The annotation format is the only supported format in this generator.
The entity generator is rendered through twig files. Support for multiple formats
 hasn't been implemented for the time being. 
 
#### `--no-blameable`
Set this option to ensure blameable isn't used.
#### `--no-timestampable`  
Set this option to ensure timestampable isn't used.
#### `--no-softdeleteable`  
Set this option to ensure softdeleteable isn't used.
#### `--behaviours`  
Use to define one of the behaviors 'softdeleteable', 'timestampable' or 'blameable'.
Can be used multiple times for adding multiple behaviours.  
Example:

    php bin/console wame:generate:entity Product --behaviours=timestampable --behaviours=blameable

#### `--no-validation`
Set this option to skip questions about validation

#### `--fields`

You can use this option the same way as doctrine's command:

    php bin/console wame:generate:entity Contact --fields="email:string(255)"

#### `--savepoint`

Ever created an entity and mistakenly choose a wrong option? Do you
start over or correct this mistake afterwards in the generated code?

This generator provides you an alternative: after every field you've added
during interactive mode, a savepoint-file will be updated. 
If you make a mistake during a new field, you can cancel the command
and execute the following command:

    php bin/console wame:generate:entity --savepoint
    
Using this command, you'll start right at the point where you can
add a new field.

Mistakes aside, this can also come in handy when you just created an
Entity and realised that you forgot to add a field. Using the
savepoint option, you can add another field and have the generator
overwrite the entity.

#### Extra fields options

Since the generator allows extra features such as relationships, enums and
validations, the --fields option has become a bit more complex.

* **Enum field**  
If you want to set an enum field, you'll use `enum` as type and must provide
the `enumType` as fieldoption. Example:  
`--fields="contact_type:enum(enumType=ContactType)"`
* **Relationship fields** (many2one, one2many, one2one, many2many)   
Each relationship requires at least that a 'targetEntity' is set. 
Depending on the relationship you can also set the 'inversedBy'
or 'mappedBy' option as well as the 'orphanRemoval'. Example:  
`--fields="customer:many2one(targetEntity=Customer inversedBy=products)"`  
Please note that the generator won't update the other entity if
inversedBy or mappedBy is set. You will still need to set the properties on that entity.
* **Validations**
You can set validations for each field by adding them as field property.
Example:  
`--fields="email:string(255 validation=NotBlank;Email)"`
Not that validation must be the same as the class name of the validation.
If you want to set multiple validations, these must be seperated by a
semicolon `;`
* **Display**  
To have the generator add the `__toString()` method, you can specify the
display option for a field. This field will then be used in the toString
method. Example:  
`--fields="name:string(255 display)"`
* **Default**  
If you want a property to have a default value, you can use this option.
This option will automatically be converted to a string in case of a
string return-type. In case of a \DateTime return-type, the default
value will be used a constructor in the DateTime. For example the
value 'today' would result in new \DateTime('today'). 
For other return types the default value won't be converted.

#### fields option with json

The --fields option can quickly become a mess if you're adding several
fields or if you're using many settings. 
Moreover, the used syntax has limitations that make it impossible to
add options to you validation. What if you want to set a Range validation?

For more complexity or better overview you can use a json-like syntax, like
the following example:

    php bin/console wame:generate:entity Contact --fields="{
        email:{
            type:string,
            nullable,
            validation:{
                Email,
                NotBlank,
                Length: {
                    min: 5,
                    max: 150,
                    minMessage: We do not believe an email can have fewer than 5 characters.,
                    maxMessage: For some inexplicable emails longer than 150 chars are not allowed here.
                }
            }
        }
    }"

Saying 'json-like' is because the above example isn't actual json.
You can use actual json-syntax if you want, but the entire --fields 
option must be parsed as a string. Therefore all quotes would have to be escaped.

The generator allows you to not specify values. It will automatically
assume that keys without values are booleans with value true. 
(nullable would become nullable: true).

Depending on your console you can run the command exactly as the example above, but
you can also run as one line of code. The generator will automatically
strip newlines, tabs and too many spaces.

Keep in mind that strings must be escape if they contain characters that would
otherwise be interpreted as json, such as `,` and `}`. 

#### Name conversions

The generator will automatically convert property names and column names.

For many2one relationships, the column name will always end with
'_id'.

Some examples:

|input|property name|column name|
|--------|--------------------|-----------------|
|contact_info|contactInfo|contact_info|
|contactInfo|contactInfo|contact_info|
|Contact_Info|contactInfo|contact_info|
|**many2one:**|
|product_row_id|productRow|product_row_id|
|product_row|productRow|product_row_id|
|productRow|productRow|product_row_id|


### Translation file

You might not need to use multiple languages, but a translation file still
 comes in handy when you want to define names, such as the properties
of an entity.

For every entity you create, the translation file will be updated
with all properties of that entity, plus some default page titles
will be added.

### Example

For the interactive features, you can simply run the command and see
what happens. For non-interactive mode, the example below shows most of
the features that are included.

Product entity:

    php bin/console wame:generate:entity Product -n --fields="{
        name: {
            type: string,
            display,
            unique,
            validations: {
                NotBlank
            }
        },
        type: {
            type: enum,
            enumType: ProductType,
        },
        price {
            type: decimal,
            precision: 5,
            scale: 2,
            validation: {
                Range: {
                    min: 0,
                    max: 3500
                }
            }
        },
        firstDateOnMarket: {
            type: date
            validation: {
                Date,
                LessThanOrEqual: {
                    value: today,
                    message: \"We do not have products that aren't on the market already, so a future date is not possible\"
                }
            },
        },
        productCategory: {
            type: many2one,
            targetEntity: ProductCategory,
            validation: {
                Valid
            }
        },
        productRows: {
            type: one2many,
            targetEntity: ProductRow,
            mappedBy: product,
            orphanRemoval
        },
        productContactInfo: {
            type: one2one,
            targetEntity: ProductContactInfo,
            mappedBy: product
        },
        productSales: {
            type: many2many,
            targetEntity: productSale,
            inversedBy: products,
            validation: {
                Valid
            }
        },
    }" --behaviours=blameable --behaviours=timestampable --behaviours=softdeleteable
