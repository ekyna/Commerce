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
        if ($quote !== $this->quote) {
            if ($previous = $this->quote) {
                $this->quote = null;
                $previous->removePayment($this);
            }

            if ($this->quote = $quote) {
                $this->quote->addPayment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLocale(): ?string
    {
        if ($this->quote) {
            return $this->quote->getLocale();
        }

        return null;
    }
}
