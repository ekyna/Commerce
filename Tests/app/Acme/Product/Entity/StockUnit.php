<?php

declare(strict_types=1);

namespace Acme\Product\Entity;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
    protected ?Product $product = null;


    public function setProduct(?Product $product): StockUnitInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setSubject(?StockSubjectInterface $subject): StockUnitInterface
    {
        if ($subject && !$subject instanceof Product) {
            throw new UnexpectedTypeException($subject, Product::class);
        }

        return $this->setProduct($subject);
    }

    public function getSubject(): ?StockSubjectInterface
    {
        return $this->getProduct();
    }
}
