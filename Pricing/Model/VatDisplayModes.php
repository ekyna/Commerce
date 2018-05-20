<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

/**
 * Class VatDisplayModes
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatDisplayModes
{
    const MODE_NET = 'net';
    const MODE_ATI = 'ati';


    /**
     * Returns the vat display modes.
     *
     * @return array
     */
    public static function getModes()
    {
        return [
            static::MODE_NET,
            static::MODE_ATI,
        ];
    }
}
