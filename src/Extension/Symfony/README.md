# Symfony Extension
This extension allows you to serialize/deserialize Kronika objects 
with [Symfony Serializer](https://github.com/symfony/serializer).

## Installation
Register the handlers to Symfony Serializer 
([documentation](https://symfony.com/doc/current/serializer/custom_normalizer.html#registering-it-in-your-application)).

```php
use Kronika\Extension\Symfony\Serializer\KronikaNormalizers;
use Symfony\Component\Serializer\Serializer;

$serializer = new Serializer(
    normalizers: KronikaNormalizers::build(),
);
```
