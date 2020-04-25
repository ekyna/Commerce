<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItemAdjustment;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;

/**
 * Class QuoteItemAdjustment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemAdjustment extends AbstractSaleItemAdjustment implements QuoteItemAdjustmentInterface
{
    /**
     * @inheritDoc
     */
    protected function assertSaleItemClass(SaleItemInterface $item): void
    {
        if (!$item instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . QuoteItemInterface::class);
        }
    }
}
