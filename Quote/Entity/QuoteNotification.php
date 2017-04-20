<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractNotification;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteNotificationInterface;

/**
 * Class QuoteNotification
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteNotification extends AbstractNotification implements QuoteNotificationInterface
{
    protected ?QuoteInterface $quote = null;


    public function getSale(): ?SaleInterface
    {
        return $this->getQuote();
    }

    public function setSale(?SaleInterface $sale): SaleNotificationInterface
    {
        if ($sale && !$sale instanceof QuoteInterface) {
            throw new UnexpectedTypeException($sale, QuoteInterface::class);
        }

        return $this->setQuote($sale);
    }

    public function getQuote(): ?QuoteInterface
    {
        return $this->quote;
    }

    public function setQuote(?QuoteInterface $quote): QuoteNotificationInterface
    {
        if ($quote === $this->quote) {
            return $this;
        }

        if ($previous = $this->quote) {
            $this->quote = null;
            $previous->removeNotification($this);
        }

        if ($this->quote = $quote) {
            $this->quote->addNotification($this);
        }

        return $this;
    }
}
