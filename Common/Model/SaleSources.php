<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class SaleSources
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleSources
{
    const SOURCE_WEBSITE     = 'website';
    const SOURCE_MARKETPLACE = 'marketplace';
    const SOURCE_COMMERCIAL  = 'commercial';


    /**
     * Returns the sources.
     *
     * @return array
     */
    static public function getSources()
    {
        return [
            static::SOURCE_WEBSITE,
            static::SOURCE_MARKETPLACE,
            static::SOURCE_COMMERCIAL,
        ];
    }
}
