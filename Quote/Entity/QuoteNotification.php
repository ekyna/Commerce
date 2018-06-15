<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteNotificationInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteNotification
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteNotification extends AbstractNotification implements QuoteNotificationInterface
{
    /**
     * @var QuoteInterface
     */
    protected $quote;


    /**
     * @inheritDoc
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @inheritDoc
     */
    public function setQuote(QuoteInterface $quote = null)
    {
        if ($quote !== $this->quote) {
            if ($previous = $this->quote) {
                $this->quote = null;
                $previous->removeNotification($this);
            }

            if ($this->quote = $quote) {
                $this->quote->addNotification($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSale()
    {
        return $this->getQuote();
    }

    /**
     * @inheritDoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        return $this->setQuote($sale);
    }
}
