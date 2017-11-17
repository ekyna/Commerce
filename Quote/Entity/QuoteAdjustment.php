<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAdjustment;
use Ekyna\Component\Commerce\Quote\Model\QuoteAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteAdjustment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustment extends AbstractSaleAdjustment implements QuoteAdjustmentInterface
{
    /**
     * @var QuoteInterface
     */
    protected $quote;


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
        if ($quote !== $this->quote) {
            $previous = $this->quote;
            $this->quote = $quote;

            if ($previous) {
                $previous->removeAdjustment($this);
            }

            if ($this->quote) {
                $this->quote->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable()
    {
        return $this->quote;
    }
}
