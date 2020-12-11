<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

/**
 * Class ShipmentMethod
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethod extends AbstractMethod implements Shipment\ShipmentMethodInterface
{
    use TaxableTrait;

    /**
     * @var string
     */
    protected $platformName;

    /**
     * @var string
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
    public function getPlatformName()
    {
        return $this->platformName;
    }

    /**
     * @inheritdoc
     */
    public function setPlatformName($name)
    {
        $this->platformName = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @inheritdoc
     */
    public function setGatewayName($name)
    {
        $this->gatewayName = $name;

        return $this;
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
    public function setGatewayConfig(array $config = null)
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
            $this->prices->add($price);
            $price->setMethod($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePrice(Shipment\ShipmentPriceInterface $price)
    {
        if ($this->hasPrice($price)) {
            $this->prices->removeElement($price);
            $price->setMethod(null);
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
    protected function getTranslationClass(): string
    {
        return ShipmentMethodTranslation::class;
    }
}
