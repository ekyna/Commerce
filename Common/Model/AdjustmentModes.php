<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class AdjustmentModes
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AdjustmentModes
{
    public const MODE_FLAT = 'flat';
    public const MODE_PERCENT = 'percent';


    public static function getModes(): array
    {
        return [
            AdjustmentModes::MODE_FLAT,
            AdjustmentModes::MODE_PERCENT,
        ];
    }

    public static function isValidMode(string $mode, bool $throw = true): bool
    {
        if (in_array($mode, AdjustmentModes::getModes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException('Invalid adjustment mode.');
        }

        return false;
    }

    private function __construct()
    {
    }
}
