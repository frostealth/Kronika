# JMS Serializer Extension
This extension allows you to serialize/deserialize Kronika objects in a more effective
and flexible way with [JMS Serializer](https://github.com/schmittjoh/serializer).

For example:
```php
use Kronika\Time;

final readonly class Foo
{
    public function __construct(
        #[Type(Time::class)]
        private Time $time,
    ) {
    }
}

echo $serializer->serialize(new Foo(Time::of(12, 30, 45)));
```
The result **without** the extension:
```json
{
  "time": {
    "hour": {
      "value": 12
     },
     "minute": {
       "value": 30
      },
      "second": {
        "value": 45,
        "micro": 0
      }
  }
}
```
The result **with** the extension:
```json
{
  "time": "12:30:45.000000"
}
```

## Installation
Register a custom handler to JMS Serializer ([documentation](https://jmsyst.com/libs/serializer/master/handlers)).

```php
$serializer = JMS\Serializer\SerializerBuilder::create()
    ->addDefaultHandlers()
    ->configureHandlers(function(JMS\Serializer\Handler\HandlerRegistry $registry) {
        $registry->registerSubscribingHandler(new \Kronika\Extension\JmsSerializer\KronikaSubscribingHandler());
    })
    ->build()
;
```
