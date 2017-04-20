<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleAdjustment;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteAdjustmentInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteAdjustment
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustment extends AbstractSaleAdjustment implements QuoteAdjustmentInterface
{
    protected ?QuoteInterface $quote = null;


    public function getSale(): ?SaleInterface
    {
        return $this->quote;
    }

    public function getQuote(): ?QuoteInterface
    {
        return $this->quote;
    }

    public function setQuote(?QuoteInterface $quote): QuoteAdjustmentInterface
    {
        if ($quote === $this->quote) {
            return $this;
        }

        if ($previous = $this->quote) {
            $this->quote = null;
            $previous->removeAdjustment($this);
        }

        if ($this->quote = $quote) {
            $this->quote->addAdjustment($this);
        }

        return $this;
    }

    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->quote;
    }
}
