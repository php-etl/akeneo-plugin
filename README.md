Akeneo Data Flows
===

Principles
---

This package aims at integration the Akeneo PHP clients into the
[Pipeline](https://github.com/php-etl/pipeline) stack. This integration is
compatible with both [akeneo/api-php-client-ee](https://github.com/akeneo/api-php-client-ee)
and [akeneo/api-php-client](https://github.com/akeneo/api-php-client).

The tools in this library will produce an AST using [nikic/php-parser](https://github.com/nikic/PHP-Parser)
that you will be able to print in order to have some generated optimized glue code.

Configuration format
---



Usage
---

This library will build for you either an extractor or a loader, compatible with the Akeneo API.

```php
echo (new PhpParser\PrettyPrinter\Standard())->prettyPrint(
    (new \Kiboko\Component\ETL\Flow\Akeneo\Configurator\ServiceFactory())
        ->compile([
            'akeneo' => [
                'enterprise' => true,
                'endpoint' => [
                    'type' => 'productModel',
                    'method' => 'listPerPage',
                ],
                'client' => [
                    'context' => [
                        'http_client' => 'Http\\Mock\\Client',
                        'http_request_factory' => 'Foo\\Mock\\RequestFactory::bar',
                        'http_stream_factory' => 'Foo\\Mock\\StreamFactory::foo',
                        'filesystem' => 'Foo\\Mock\\Filesystem',
                    ],
                    'api_url' => 'https://demo.akeneo.com',
                    'client_id' => '1234567890',
                    'secret' => 'qwertyuiop',
                    'username' => 'johndoe',
                    'password' => 'lkjhgfdsa',
                ],
            ],
        ]),
    );
```

See also
---

* [php-etl/pipeline](https://github.com/php-etl/pipeline)
* [php-etl/fast-map](https://github.com/php-etl/fast-map)
* [php-etl/akeneo-expression-language](https://github.com/php-etl/akeneo-expression-language)
