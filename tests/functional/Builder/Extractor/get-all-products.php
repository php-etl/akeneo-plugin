<?php declare(strict_types=1);

return new \GuzzleHttp\Psr7\Response(200, [], \GuzzleHttp\Psr7\Utils::streamFor(fopen(__DIR__.'/products.all.json', 'r')));
