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
     * @var Item[]
     */
    public $children = [];

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
    public $unitPrice;

    /**
     * @var float
     */
    public $quantity;

    /**
     * @var float
     */
    public $basePrice;

    /**
     * @var TaxAmount[]
     */
    public $taxAmounts;

    /**
     * @var float
     */
    public $totalPrice;
}
