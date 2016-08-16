<?php

namespace Ekyna\Component\Commerce\Order\View;

/**
 * Class Order
 * @package Ekyna\Component\Commerce\Order\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order
{
    /**
     * @var float
     */
    public $baseTotal;

    /**
     * @var TaxAmount[]
     */
    public $taxAmounts;

    /**
     * @var float
     */
    public $granTotal;
}
