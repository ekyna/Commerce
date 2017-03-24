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
     *
     * @return Model\QuoteInterface
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
        if ($quote != $this->quote) {
            $previous = $this->quote;
            $this->quote = $quote;

            if ($previous) {
                $previous->removePayment($this);
            }

            if ($this->quote) {
                $this->quote->addPayment($this);
            }
        }

        return $this;
    }
}
