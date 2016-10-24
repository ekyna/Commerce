<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAdjustment;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;

/**
 * Class QuoteItemAdjustment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemAdjustment extends AbstractAdjustment implements QuoteItemAdjustmentInterface
{
    /**
     * @var QuoteItemInterface
     */
    protected $item;


    /**
     * @inheritdoc
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @inheritdoc
     */
    public function setItem(QuoteItemInterface $item = null)
    {
        if (null !== $this->item && $this->item != $item) {
            $this->item->removeAdjustment($this);
        }

        $this->item = $item;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable()
    {
        return $this->item;
    }
}
