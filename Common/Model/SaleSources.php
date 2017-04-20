<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class SaleSources
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleSources
{
    public const SOURCE_WEBSITE     = 'website';
    public const SOURCE_MARKETPLACE = 'marketplace';
    public const SOURCE_COMMERCIAL  = 'commercial';


    public static function getSources(): array
    {
        return [
            SaleSources::SOURCE_WEBSITE,
            SaleSources::SOURCE_MARKETPLACE,
            SaleSources::SOURCE_COMMERCIAL,
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
