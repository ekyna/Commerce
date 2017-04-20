<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Class VatDisplayModes
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatDisplayModes
{
    public const MODE_NET = 'net';
    public const MODE_ATI = 'ati';


    /**
     * Returns the vat display modes.
     */
    public static function getModes(): array
    {
        return [
            static::MODE_NET,
            static::MODE_ATI,
        ];
    }
}
