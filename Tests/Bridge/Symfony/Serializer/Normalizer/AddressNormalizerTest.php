<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\AddressNormalizer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Generator;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

/**
 * Class AddressNormalizerTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Symfony\Serializer\Normalizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressNormalizerTest extends TestCase
{
    private AddressNormalizer $normalizer;

    protected function setUp(): void
    {
        $repository = $this->createMock(CountryRepositoryInterface::class);

        $this->normalizer = new AddressNormalizer($repository, PhoneNumberUtil::getInstance());
    }

    /**
     * @dataProvider provideNormalize
     */
    public function testNormalize(
        array            $expected,
        AddressInterface $model,
        string           $format = null,
        array            $context = []
    ): void {
        $normalized = $this->normalizer->normalize($model, $format, $context);

        self::assertEquals($expected, $normalized);
    }

    public function provideNormalize(): Generator
    {
        $data = [
            'street'      => '1 rue quelconque',
            'postal_code' => '12345',
            'city'        => 'Ville',
            'country'     => 'FR',
        ];

        $address = Fixture::address([
            'street'      => '1 rue quelconque',
            'postal_code' => '12345',
            'city'        => 'Ville',
            'country'     => Fixture::COUNTRY_FR,
        ]);

        yield [
            $data,
            $address,
        ];

        $data = [
            'street'      => '1 avenue aléatoire',
            'postal_code' => '98765',
            'city'        => 'Métropole',
            'country'     => 'FR',
            'phone'       => '+33 1 01 01 01 01',
        ];

        $address = Fixture::address([
            'street'      => '1 avenue aléatoire',
            'postal_code' => '98765',
            'city'        => 'Métropole',
            'country'     => Fixture::COUNTRY_FR,
            'phone'       => '+330101010101',
        ]);

        yield [
            $data,
            $address,
        ];

        $data = [
            'street'        => '1 avenue aléatoire',
            'postal_code'   => '98765',
            'city'          => 'Métropole',
            'country'       => 'FR',
            'phone'         => '01 01 01 01 01',
            'phone_country' => 'FR',
        ];

        $address = Fixture::address([
            'street'      => '1 avenue aléatoire',
            'postal_code' => '98765',
            'city'        => 'Métropole',
            'country'     => Fixture::COUNTRY_FR,
            'phone'       => '+330101010101',
        ]);

        yield [
            $data,
            $address,
            null,
            ['groups' => ['AddressChoice']],
        ];
    }

    public function testDenormalize(): void
    {
    }
}
