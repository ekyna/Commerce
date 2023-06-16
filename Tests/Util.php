<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\Revenue;
use Generator;

/**
 * Class Util
 * @package Ekyna\Component\Commerce\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Util
{
    public static function decimal(array $data, string|int $key): Decimal
    {
        return new Decimal((string)($data[$key] ?? 0));
    }

    public static function amount(array $data, bool $single = false): Amount
    {
        if ($single && isset($data['_single'])) {
            $data = $data['_single'];
        }

        // TODO Adjustments (taxes & discounts)

        return new Amount(
            $data['currency'] ?? Fixture::CURRENCY_EUR,
            self::decimal($data, 'unit'),
            self::decimal($data, 'gross'),
            self::decimal($data, 'discount'),
            self::decimal($data, 'base'),
            self::decimal($data, 'tax'),
            self::decimal($data, 'total'),
        );
    }

    public static function itemsAmountsMap(array $items, bool $single): Generator
    {
        foreach ($items as $item) {
            yield $item['_reference'] => [
                Fixture::get($item['_reference']),
                null,
                $single,
                !$single,
                Util::amount($item['_amount'], $single),
            ];

            if (!empty($item['children'])) {
                yield from self::itemsAmountsMap($item['children'], $single);
            }
        }
    }

    public static function discountsAmountsMap(array $discounts): Generator
    {
        foreach ($discounts as $discount) {
            yield $discount['_reference'] => [
                Fixture::get($discount['_reference']),
                null,
                null,
                Util::amount($discount['_amount']),
            ];
        }
    }

    public static function cost(array $data, bool $single = false): Cost
    {
        if ($single && isset($data['_single'])) {
            $data = $data['_single'];
        }

        return new Cost(
            self::decimal($data, 'product'),
            self::decimal($data, 'supply'),
            self::decimal($data, 'shipment'),
            $data['average'] ?? false,
        );
    }

    public static function itemsCostsMap(array $items, bool $single): Generator
    {
        foreach ($items as $item) {
            yield $item['_reference'] => [
                Fixture::get($item['_reference']),
                null,
                $single,
                Util::cost($item['_cost'], $single),
            ];

            if (!empty($item['children'])) {
                yield from self::itemsCostsMap($item['children'], $single);
            }
        }
    }

    public static function revenue(array $data): Revenue
    {
        return new Revenue(
            self::decimal($data, 'product'),
            self::decimal($data, 'shipment'),
        );
    }

    public static function margin(array $data, bool $single = false): Margin
    {
        if ($single && isset($data['_single'])) {
            $data = $data['_single'];
        }

        $revenue = $data['revenue'] ?? [];
        $cost = $data['cost'] ?? [];

        return new Margin(
            self::decimal($revenue, 'product'),
            self::decimal($revenue, 'shipment'),
            self::decimal($cost, 'product'),
            self::decimal($cost, 'supply'),
            self::decimal($cost, 'shipment'),
            $cost['average'] ?? false,
        );
    }
}
