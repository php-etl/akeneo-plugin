Akeneo Data Flows
===

[![Quality (PHPStan lvl 4)](https://github.com/php-etl/akeneo-plugin/actions/workflows/quality.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/quality.yaml)
[![PHPUnit](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpunit.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpunit.yaml)
[![Infection](https://github.com/php-etl/akeneo-plugin/actions/workflows/infection.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/infection.yaml)
[![PHPStan level 5](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-5.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-5.yaml)
[![PHPStan level 6](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-6.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-6.yaml)
[![PHPStan level 7](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-7.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-7.yaml)
[![PHPStan level 8](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-8.yaml/badge.svg)](https://github.com/php-etl/akeneo-plugin/actions/workflows/phpstan-8.yaml)
![PHP](https://img.shields.io/packagist/php-v/php-etl/akeneo-plugin)

Goal
---

This package aims at integration the Akeneo PHP clients into the
[Pipeline](https://github.com/php-etl/pipeline) stack. This integration is
compatible with the [Akeneo client](https://github.com/akeneo/api-php-client)

Principles
---

The tools in this library will produce executable PHP sources, using an intermediate _Abstract Syntax Tree_ from
[nikic/php-parser](https://github.com/nikic/PHP-Parser). This intermediate format helps you combine 
the code produced by this library with other packages from [Middleware](https://github.com/php-etl).

Configuration format
---

### Building an extractor

```yaml
akeneo:
  extractor:
    type: productModel
    method: all
    search:
      - { field: enabled, operator: '=', value: true }
      - { field: completeness, operator: '>', value: 70, scope: ecommerce }
      - { field: completeness, operator: '<', value: 85, scope: ecommerce }
      - { field: categories, operator: IN, value: winter_collection }
      - { field: family, operator: IN, value: [camcorders, digital_cameras] }
  logger:
    type: 'stderr'
  client:
    api_url: 'https://demo.akeneo.com'
    client_id: '2_5a3jtcvwi8w0cwk88w04ogkcks00o4wowwgc8gg4w0cow4wsc8'
    secret: '4ww9l30ij2m8wsw8w04sgw4wgkwc8gss0sgc8cc0o0goo4wkso'
    username: 'demo_9573'
    password: 516f3e3e5
```

### Building a loader

```yaml
akeneo:
  loader:
    type: productModel
    method: upsert
  logger:
    type: 'stderr'
  client:
    api_url: 'https://demo.akeneo.com'
    client_id: '2_5a3jtcvwi8w0cwk88w04ogkcks00o4wowwgc8gg4w0cow4wsc8'
    secret: '4ww9l30ij2m8wsw8w04sgw4wgkwc8gss0sgc8cc0o0goo4wkso'
    username: 'demo_9573'
    password: 516f3e3e5
```

Usage
---

This library will build for you either an extractor or a loader, compatible with the Akeneo API.

You can use the following PHP script to test and print the result of your configuration.

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Kiboko\Component\Flow\Akeneo;
use PhpParser\Node;
use PhpParser\PrettyPrinter;
use Symfony\Component\Console;
use Symfony\Component\Yaml;

$input = new Console\Input\ArgvInput($argv);
$output = new Console\Output\ConsoleOutput();

class DefaultCommand extends Console\Command\Command
{
    protected static $defaultName = 'test';

    protected function configure()
    {
        $this->addArgument('file', Console\Input\InputArgument::REQUIRED);
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $factory = new Akeneo\Service();

        $style = new Console\Style\SymfonyStyle(
            $input,
            $output,
        );

        $config = Yaml\Yaml::parse(input: file_get_contents($input->getArgument('file')));

        $style->section('Validation');
        $style->writeln($factory->validate($config) ? '<info>ok</info>' : '<error>failed</error>');
        $style->section('Normalized Config');
        $style->writeln(\json_encode($config = $factory->normalize($config), JSON_PRETTY_PRINT));
        $style->section('Generated code');
        $style->writeln((new PrettyPrinter\Standard())->prettyPrintFile([
            new Node\Stmt\Return_($factory->compile($config)->getNode()),
        ]));

        return 0;
    }
}

(new Console\Application())
    ->add(new DefaultCommand())
    ->run($input, $output)
;
```

See also
---

* [php-etl/pipeline](https://github.com/php-etl/pipeline)
* [php-etl/fast-map](https://github.com/php-etl/fast-map)
* [php-etl/akeneo-expression-language](https://github.com/php-etl/akeneo-expression-language)
