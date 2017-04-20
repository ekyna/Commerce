<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Entity\AbstractPayment;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class QuotePayment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuotePayment extends AbstractPayment implements Model\QuotePaymentInterface
{
    protected ?Model\QuoteInterface $quote = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getQuote();
    }

    public function getQuote(): ?Model\QuoteInterface
    {
        return $this->quote;
    }

    public function setQuote(Model\QuoteInterface $quote = null): Model\QuotePaymentInterface
    {
        if ($quote === $this->quote) {
            return $this;
        }

        if ($previous = $this->quote) {
            $this->quote = null;
            $previous->removePayment($this);
        }

        if ($this->quote = $quote) {
            $this->quote->addPayment($this);
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        if ($this->quote) {
            return $this->quote->getLocale();
        }

        return null;
    }
}
