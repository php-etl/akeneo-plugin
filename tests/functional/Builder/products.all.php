<?php declare(strict_types=1);

return new \Laminas\Diactoros\Response(body: new \Laminas\Diactoros\Stream(__DIR__.'/body.json'), status: 200);
