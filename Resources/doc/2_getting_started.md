WameGeneratorBundle
=====================

1.  [Introduction](1_introduction.md)
2.  **Getting started**
3.  [Configuration options](3_configuration.md)
4.  [Entity Generation](4_entity_generation.md)
5.  [CRUD Generation](5_crud_generation.md)
6.  [Enum Generation](6_enum_generation.md)
7.  [Form Generation](7_form_generation.md)
8.  [Voter Generation](8_voter_generation.md)
9.  [Datatable Generation](9_datatable_generation.md)
10. [Overriding twig files](10_overriding_twig.md)
11. [Extending this bundle](11_extending_bundle.md)


## Getting started
Install the bundle for the dev-environment using composer.

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


## Required / Recommended bundles

The following bundles are assumed to be installed and configured:
- [DatatablesBundle](https://github.com/stwe/DatatablesBundle)
for datatables.  
If you do not wish to use datatables, you could add 
`wame_generator.enable_datatables: false` to your config.yml, so
that you won't be bothered with the question of using datatables.  
Note that the DatatablesBundle requires that the [FOSJsRoutingBundle](https://symfony.com/doc/master/bundles/FOSJsRoutingBundle/installation.html)
is installed.
- [StofDoctrineExtensionsBundle](http://symfony.com/doc/master/bundles/StofDoctrineExtensionsBundle/index.html)
for Gedmo trait-options. 
If you do not wish to use these traits, you could add
`wame_generator.enable_traits: false` to your config.yml, so
that you won't be bothered with questions about using traits.
- [DoctrineEnumBundle](https://github.com/fre5h/DoctrineEnumBundle)
 for enumerables.  
 If you do not intent to use enum, simply do not use it when asked for a
 property type.

For disabling the bundles in the config files, see  
[Configuration options](3_configuration.md).