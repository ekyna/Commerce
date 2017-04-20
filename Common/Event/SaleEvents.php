<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

/**
 * Class SaleEvents
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SaleEvents
{
    public const DISCOUNT = 'ekyna_commerce.sale.discount';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
