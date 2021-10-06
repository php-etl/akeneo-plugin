<?php

declare(strict_types=1);

namespace functional\Capacity\Lookup;

use Kiboko\Plugin\Akeneo\Capacity\Lookup\All;
use Kiboko\Plugin\Akeneo\InvalidParameterException;
use Kiboko\Plugin\Akeneo\MissingParameterException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AllTest extends TestCase
{

    public function testExceptionNotThrownProductApiWithoutCode(): void
    {
        $capacity = new All(new ExpressionLanguage());

        $capacity->getBuilder(
            [
                'type' => 'product',
                'method' => 'all',
            ]
        );
        $this->assertTrue(true);
    }

    public function testExceptionThrownProductApiWithCode(): void
    {
        $this->expectException(InvalidParameterException::class);
        $capacity = new All(new ExpressionLanguage());
        $capacity->getBuilder(
            [
                'type' => 'product',
                'method' => 'all',
                'code' => 'test',
            ]
        );
    }

    /**
     * @dataProvider specificApiProvider
     */
    public function testExceptionThrownWhenCodeNeeded($api): void
    {
        $this->expectException(MissingParameterException::class);
        $capacity = new All(new ExpressionLanguage());
        $capacity->getBuilder(
            [
                'type' => $api,
                'method' => 'all',
            ]
        );
    }

    public function specificApiProvider()
    {
        return [
            ['familyVariant'],
            ['attributeOption'],
            ['referenceEntity'],
            ['referenceEntityAttribute'],
            ['referenceEntityRecord'],
        ];
    }
}
