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
| 10. [Overriding twig files](10_overriding_twig.md#wamegeneratorbundle)
| **11. Extending this bundle**


## Extending this bundle

The ideal situation is finding a generator that fits your exact needs. 
Depending on what you want this may not be very likely, so you may have
to create your own generator or find a generator you want to override.

The SensioGeneratorBundle contains lots of usefull features, but
a huge drawback is it's impossible to extend due to the fact
many methods are private or contain way too many lines of codes. Adding
a small piece of own code would quickly result in completely rewriting large
chunks of code.

As a result of this hard extensibility, the WameGeneratorBundle is
mostly rewritten code. In this process lots 
of refactoring is done to make it much easier to extend or override code.
