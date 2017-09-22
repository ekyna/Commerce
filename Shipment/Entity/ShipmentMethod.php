<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model as Pricing;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class ShipmentMethod
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethod extends AbstractMethod implements Shipment\ShipmentMethodInterface
{
    use Pricing\TaxableTrait;

    /**
     * @var string
     */
    protected $factoryName;

    /**
     * @var array
     */
    protected $gatewayName;

    /**
     * @var array
     */
    protected $gatewayConfig;

    /**
     * @var ArrayCollection|Shipment\ShipmentPriceInterface[]
     */
    protected $prices;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->prices = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getFactoryName()
    {
        return $this->factoryName;
    }

    /**
     * @inheritdoc
     */
    public function setFactoryName($name)
    {
        $this->factoryName = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGatewayName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getGatewayConfig()
    {
        return $this->gatewayConfig;
    }

    /**
     * @inheritdoc
     */
    public function setGatewayConfig(array $config)
    {
        $this->gatewayConfig = $config;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @inheritdoc
     */
    public function hasPrices()
    {
        return 0 < $this->prices->count();
    }

    /**
     * @inheritdoc
     */
    public function hasPrice(Shipment\ShipmentPriceInterface $price)
    {
        return $this->prices->contains($price);
    }

    /**
     * @inheritdoc
     */
    public function addPrice(Shipment\ShipmentPriceInterface $price)
    {
        if (!$this->hasPrice($price)) {
            $price->setMethod($this);
            $this->prices->add($price);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePrice(Shipment\ShipmentPriceInterface $price)
    {
        if ($this->hasPrice($price)) {
            $price->setMethod(null);
            $this->prices->removeElement($price);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function validateMessageClass(MessageInterface $message)
    {
        if (!$message instanceof ShipmentMessage) {
            throw new InvalidArgumentException("Expected instance of ShipmentMessage.");
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return ShipmentMethodTranslation::class;
    }
}
