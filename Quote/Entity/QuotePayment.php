<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Model;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;

/**
 * Class QuotePayment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuotePayment extends AbstractPayment implements Model\QuotePaymentInterface
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
        return $this->getQuote();
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
        if ($this->quote && $this->quote != $quote) {
            $this->quote->removePayment($this);
        }

        $this->quote = $quote;

        return $this;
    }
}
