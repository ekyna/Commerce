<?php

namespace Acme\Product\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockUnit
 * @package Acme\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnit extends AbstractStockUnit
{
    /**
     * @var Product
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function setProduct(Product $product): StockUnitInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(StockSubjectInterface $subject): StockUnitInterface
    {
        if (!$subject instanceof Product) {
            throw new InvalidArgumentException("Expected instance of Product.");
        }

        return $this->setProduct($subject);
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): ?StockSubjectInterface
    {
        return $this->getProduct();
    }
}
