<?php declare(strict_types=1);

return new \Laminas\Diactoros\Response(body: new \Laminas\Diactoros\Stream(__DIR__.'/product.json'), status: 200);
