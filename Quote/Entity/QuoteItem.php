<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class QuoteItem
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItem extends AbstractSaleItem implements Model\QuoteItemInterface
{
    /**
     * @var Model\QuoteInterface
     */
    protected $quote;


    /**
     * @inheritdoc
     */
    public function getSale()
    {
        if (null === $quote = $this->getQuote()) {
            $parent = $this;
            while (null !== $parent) {
                if (null !== $quote = $parent->getQuote()) {
                    return $quote;
                }
                $parent = $parent->getParent();
            }
        }

        return $quote;
    }

    /**
     * @inheritdoc
     */
    public function setSale(Common\SaleInterface $sale = null)
    {
        $sale && $this->assertSaleClass($sale);

        $this->setQuote($sale);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @inheritdoc
     */
    public function setQuote(Model\QuoteInterface $quote = null)
    {
        if ($quote !== $this->quote) {
            if ($previous = $this->quote) {
                $this->quote = null;
                $previous->removeItem($this);
            }

            if ($this->quote = $quote) {
                $this->quote->addItem($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function assertSaleClass(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Model\QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\QuoteInterface::class);
        }
    }

    /**
     * @inheritdoc
     */
    protected function assertItemClass(Common\SaleItemInterface $child)
    {
        if (!$child instanceof Model\QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\QuoteItemInterface::class);
        }
    }

    /**
     * @inheritdoc
     */
    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\QuoteItemAdjustmentInterface::class);
        }
    }
}
