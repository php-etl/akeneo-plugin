<?php declare(strict_types=1);

return new \Laminas\Diactoros\Response(body: new \Laminas\Diactoros\Stream(__DIR__.'/products.all.json'), status: 200);
