<?php

namespace Ekyna\Component\Commerce\Product\Event;

use Ekyna\Component\Commerce\Product\Model\ProductEventInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;

/**
 * Class ProductEvent
 * @package Ekyna\Component\Commerce\Product\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEvent implements ProductEventInterface
{
    /**
     * @var ProductInterface
     */
    private $product;


    /**
     * Constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }
}
