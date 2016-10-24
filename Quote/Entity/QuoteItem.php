<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;

/**
 * Class QuoteItem
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItem extends AbstractSaleItem implements QuoteItemInterface
{
    /**
     * @var QuoteInterface
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
    public function setSale(SaleInterface $sale = null)
    {
        if (null !== $sale && !$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException('Expected instance of QuoteInterface');
        }

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
    public function setQuote(QuoteInterface $quote = null)
    {
        if (null !== $this->quote && $this->quote != $quote) {
            $this->quote->removeItem($this);
        }

        $this->quote = $quote;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParent(SaleItemInterface $parent = null)
    {
        if (!$parent instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        if (null !== $this->parent && $this->parent != $parent) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addChild(SaleItemInterface $child)
    {
        if (!$child instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChild(SaleItemInterface $child)
    {
        if (!$child instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface.");
        }

        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            //$child->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemAdjustmentInterface.");
        }

        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setItem($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemAdjustmentInterface.");
        }

        if ($this->adjustments->contains($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            //$adjustment->setItem(null);
        }

        return $this;
    }
}
