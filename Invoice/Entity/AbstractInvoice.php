<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class AbstractInvoice
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoice implements Invoice\InvoiceInterface
{
    use Common\NumberSubjectTrait,
        TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var array
     */
    protected $customer;

    /**
     * @var array
     */
    protected $invoiceAddress;

    /**
     * @var array
     */
    protected $deliveryAddress;

    /**
     * @var ArrayCollection|Invoice\InvoiceLineInterface[]
     */
    protected $lines;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $goodsBase;

    /**
     * @var float
     */
    protected $shipmentBase;

    /**
     * @var float
     */
    protected $taxesTotal;

    /**
     * @var float
     */
    protected $grandTotal;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Invoice\InvoiceTypes::TYPE_INVOICE;
        $this->lines = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getNumber();
    }

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Returns the customer data.
     *
     * @return array
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets the customer data.
     *
     * @param array $data
     */
    public function setCustomer(array $data)
    {
        $this->customer = $data;
    }

    /**
     * Returns the invoice address data.
     *
     * @return array
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * Sets the invoice address data.
     *
     * @param array $data
     */
    public function setInvoiceAddress(array $data)
    {
        $this->invoiceAddress = $data;
    }

    /**
     * Returns the delivery address data.
     *
     * @return array
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Sets the delivery address data.
     *
     * @param array|null $data
     */
    public function setDeliveryAddress(array $data = null)
    {
        $this->deliveryAddress = $data;
    }

    /**
     * @inheritdoc
     */
    public function hasLines()
    {
        return 0 < $this->lines->count();
    }

    /**
     * @inheritdoc
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @inheritdoc
     */
    public function getLinesByType($type)
    {
        if (!Invoice\InvoiceLineTypes::isValidType($type)) {
            throw new InvalidArgumentException("Invalid invoice line type.");
        }

        $lines = [];

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @inheritdoc
     */
    public function hasLine(Invoice\InvoiceLineInterface $line)
    {
        return $this->lines->contains($line);
    }

    /**
     * @inheritdoc
     */
    public function addLine(Invoice\InvoiceLineInterface $line)
    {
        if (!$this->hasLine($line)) {
            $this->lines->add($line);
            $line->setInvoice($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeLine(Invoice\InvoiceLineInterface $line)
    {
        if ($this->hasLine($line)) {
            $this->lines->removeElement($line);
            $line->setInvoice(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGoodsBase()
    {
        return $this->goodsBase;
    }

    /**
     * @inheritdoc
     */
    public function setGoodsBase($base)
    {
        $this->goodsBase = (float)$base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentBase()
    {
        return $this->shipmentBase;
    }

    /**
     * @inheritdoc
     */
    public function setShipmentBase($base)
    {
        $this->shipmentBase = (float)$base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesTotal()
    {
        return $this->taxesTotal;
    }

    /**
     * @inheritdoc
     */
    public function setTaxesTotal($total)
    {
        $this->taxesTotal = (float)$total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGrandTotal()
    {
        return $this->grandTotal;
    }

    /**
     * @inheritdoc
     */
    public function setGrandTotal($total)
    {
        $this->grandTotal = (float)$total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxesDetails()
    {
        $taxes = [];

        foreach ($this->lines as $line) {
            foreach ($line->getTaxesDetails() as $data) {
                $amount = $data['amount'] * $line->getQuantity();

                if (isset($taxes[$data['name']])) {
                    $taxes[$data['name']] += $amount;
                } else {
                    $taxes[$data['name']] = $amount;
                }
            }
        }

        return $taxes;
    }
}
