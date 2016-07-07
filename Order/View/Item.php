<?php

namespace Ekyna\Component\Commerce\Order\View;

/**
 * Class Item
 * @package Ekyna\Component\Commerce\Order\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Item
{
    /**
     * @var string
     */
    public $designation;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var float
     */
    public $netUnitPrice;

    /**
     * @var float
     */
    public $taxRate;

    /**
     * @var float
     */
    public $quantity;

    /**
     * @var float
     */
    public $netBase;

    /**
     * @var float
     */
    public $grossTotalPrice;
}
