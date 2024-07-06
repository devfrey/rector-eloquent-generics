## Installation

```bash
composer require --dev devfrey/rector-eloquent-generics
```

```php
return RectorConfig::configure()
    // ...
    ->withRules([
        Devfrey\RectorLaravel\Eloquent\AddBuilderPropertyRector::class,
        Devfrey\RectorLaravel\Eloquent\AddGenericHasBuilderTraitRector::class,
        Devfrey\RectorLaravel\Eloquent\DocumentRelationGenericsRector::class,
    ]);
```
