<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AdjustmentTypes
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentTypes
{
    public const TYPE_TAXATION = 'taxation';
    public const TYPE_INCLUDED = 'included';
    public const TYPE_DISCOUNT = 'discount';


    public static function getTypes(): array
    {
        return [
            AdjustmentTypes::TYPE_TAXATION,
            AdjustmentTypes::TYPE_INCLUDED,
            AdjustmentTypes::TYPE_DISCOUNT,
        ];
    }

    public static function isValidType(string $type, bool $throw = true): bool
    {
        if (in_array($type, AdjustmentTypes::getTypes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid adjustment type.');
        }

        return false;
    }

    private function __construct()
    {
    }
}
