<?php

namespace Ekyna\Component\Commerce\Product\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductStockUnitInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

/**
 * Class ProductStockUnit
 * @package Ekyna\Component\Commerce\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnit extends AbstractStockUnit implements ProductStockUnitInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function setSubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException("Expected instance of ProductInterface.");
        }

        return $this->setProduct($subject);
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->getProduct();
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }
}
