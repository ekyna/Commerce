<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;

/**
 * Interface QuoteItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteItemAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the quote item.
     *
     * @return QuoteItemInterface
     */
    public function getItem();

    /**
     * Sets the quote item.
     *
     * @param QuoteItemInterface $item
     * @return $this|QuoteAdjustmentInterface
     */
    public function setItem(QuoteItemInterface $item = null);
}
