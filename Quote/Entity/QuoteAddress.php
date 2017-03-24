<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Quote\Model;

/**
 * Class QuoteAddress
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddress extends AbstractAddress implements Model\QuoteAddressInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\QuoteInterface
     */
    protected $invoiceQuote;

    /**
     * @var Model\QuoteInterface
     */
    protected $deliveryQuote;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @inheritdoc
     */
    public function getInvoiceQuote()
    {
        return $this->invoiceQuote;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceQuote(Model\QuoteInterface $quote = null)
    {
        if ($quote != $this->invoiceQuote) {
            $previous = $this->invoiceQuote;
            $this->invoiceQuote = $quote;

            if ($previous) {
                $previous->setInvoiceAddress(null);
            }

            if ($this->invoiceQuote) {
                $this->invoiceQuote->setInvoiceAddress($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryQuote()
    {
        return $this->deliveryQuote;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryQuote(Model\QuoteInterface $quote = null)
    {
        if ($quote != $this->deliveryQuote) {
            $previous = $this->deliveryQuote;
            $this->deliveryQuote = $quote;

            if ($previous) {
                $previous->setDeliveryAddress(null);
            }

            if ($this->deliveryQuote) {
                $this->deliveryQuote->setDeliveryAddress($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        if (null !== $this->invoiceQuote) {
            return $this->invoiceQuote;
        } elseif (null !== $this->deliveryQuote) {
            return $this->deliveryQuote;
        }

        return null;
    }
}
