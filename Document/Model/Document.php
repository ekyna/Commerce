<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Document
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Document implements DocumentInterface
{
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
     * @var array
     */
    protected $relayPoint;

    /**
     * @var ArrayCollection|DocumentLineInterface[]
     */
    protected $lines;

    /**
     * @var string
     */
    protected $comment;

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
    protected $discountBase;

    /**
     * @var float
     */
    protected $shipmentBase;

    /**
     * @var float
     */
    protected $taxesTotal;

    /**
     * @var array
     */
    protected $taxesDetails;

    /**
     * @var float
     */
    protected $grandTotal;

    /**
     * @var SaleInterface
     */
    protected $sale;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->lines = new ArrayCollection();
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

        return $this;
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
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function setCustomer(array $data)
    {
        $this->customer = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceAddress(array $data)
    {
        $this->invoiceAddress = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryAddress(array $data = null)
    {
        $this->deliveryAddress = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * @inheritdoc
     */
    public function setRelayPoint(array $data = null)
    {
        $this->relayPoint = $data;

        return $this;
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
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException("Invalid document line type.");
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
    public function hasLine(DocumentLineInterface $line)
    {
        return $this->lines->contains($line);
    }

    /**
     * @inheritdoc
     */
    public function hasLineByType($type)
    {
        if (!DocumentLineTypes::isValidType($type)) {
            throw new InvalidArgumentException("Invalid document line type.");
        }

        foreach ($this->getLines() as $line) {
            if ($line->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function addLine(DocumentLineInterface $line)
    {
        if (!$this->hasLine($line)) {
            $this->lines->add($line);
            $line->setDocument($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeLine(DocumentLineInterface $line)
    {
        if ($this->hasLine($line)) {
            $this->lines->removeElement($line);
            $line->setDocument(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLines(ArrayCollection $lines)
    {
        foreach ($this->lines as $line) {
            if (!$lines->contains($line)) {
                $this->removeLine($line);
            }
        }

        $this->lines = new ArrayCollection();

        foreach ($lines as $line) {
            $this->addLine($line);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

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
    public function getGoodsBase(bool $ati = false)
    {
        return $ati ? $this->ati($this->goodsBase) : $this->goodsBase;
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
    public function getDiscountBase(bool $ati = false)
    {
        return $ati ? $this->ati($this->discountBase) : $this->discountBase;
    }

    /**
     * @inheritdoc
     */
    public function setDiscountBase($base)
    {
        $this->discountBase = (float)$base;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentBase(bool $ati = false)
    {
        return $ati ? $this->ati($this->shipmentBase) : $this->shipmentBase;
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
    public function getTaxesDetails()
    {
        return $this->taxesDetails;
    }

    /**
     * @inheritdoc
     */
    public function setTaxesDetails(array $details)
    {
        $this->taxesDetails = $details;

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
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * @inheritdoc
     */
    public function setSale(SaleInterface $sale = null)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasLineDiscount()
    {
        foreach ($this->lines as $line) {
            if (0 != $line->getDiscount()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasMultipleTaxes()
    {
        return 1 < count($this->taxesDetails);
    }

    /**
     * Adds the taxes to the given amount.
     *
     * @param float $amount
     *
     * @return float
     */
    private function ati(float $amount)
    {
        $result = $amount;

        foreach ($this->taxesDetails as $tax) {
            $result += $amount * $tax['rate'] / 100;
        }

        return Money::round($result, $this->currency);
    }

    /**
     * @inheritdoc
     */
    public function isAti()
    {
        return $this->getSale()->isAtiDisplayMode();
    }
}
