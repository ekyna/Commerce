<?php

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
    /**
     * @var QuoteInterface
     */
    protected $quote;


    /**
     * @inheritDoc
     */
    public function getSale(): ?SaleInterface
    {
        return $this->quote;
    }

    /**
     * @inheritdoc
     */
    public function getQuote(): QuoteInterface
    {
        return $this->quote;
    }

    /**
     * @inheritdoc
     */
    public function setQuote(QuoteInterface $quote = null): QuoteAdjustmentInterface
    {
        if ($quote !== $this->quote) {
            if ($previous = $this->quote) {
                $this->quote = null;
                $previous->removeAdjustment($this);
            }

            if ($this->quote = $quote) {
                $this->quote->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->quote;
    }
}
