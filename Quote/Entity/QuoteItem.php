<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItem;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class QuoteItem
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItem extends AbstractSaleItem implements Model\QuoteItemInterface
{
    protected ?Model\QuoteInterface $quote = null;

    public function getSale(): ?Common\SaleInterface
    {
        return $this->getQuote();
    }

    /**
     * @param Model\QuoteInterface|null $sale
     */
    public function setSale(?Common\SaleInterface $sale): Common\SaleItemInterface
    {
        if (null !== $sale) {
            $this->assertSaleClass($sale);
        }

        $this->setQuote($sale);

        return $this;
    }

    public function getQuote(): ?Model\QuoteInterface
    {
        return $this->quote;
    }

    public function setQuote(?Model\QuoteInterface $quote): Model\QuoteItemInterface
    {
        if ($quote === $this->quote) {
            return $this;
        }

        if ($previous = $this->quote) {
            $this->quote = null;
            $previous->removeItem($this);
        }

        if ($this->quote = $quote) {
            $this->quote->addItem($this);
        }

        return $this;
    }

    protected function assertSaleClass(Common\SaleInterface $sale): void
    {
        if (!$sale instanceof Model\QuoteInterface) {
            throw new UnexpectedTypeException($sale, Model\QuoteInterface::class);
        }
    }

    protected function assertItemClass(Common\SaleItemInterface $child): void
    {
        if (!$child instanceof Model\QuoteItemInterface) {
            throw new UnexpectedTypeException($child, Model\QuoteItemInterface::class);
        }
    }

    protected function assertItemAdjustmentClass(Common\AdjustmentInterface $adjustment): void
    {
        if (!$adjustment instanceof Model\QuoteItemAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\QuoteItemAdjustmentInterface::class);
        }
    }
}
