<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAdjustment;
use Ekyna\Component\Commerce\Quote\Model\QuoteAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteAdjustment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustment extends AbstractAdjustment implements QuoteAdjustmentInterface
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
        if ($this->quote && $this->quote != $quote) {
            $this->quote->removeAdjustment($this);
        }

        $this->quote = $quote;

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
