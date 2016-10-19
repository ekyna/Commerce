<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Entity\AbstractMethod;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;

/**
 * Class ShipmentMethod
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethod extends AbstractMethod implements ShipmentMethodInterface
{
    /**
     * @var ArrayCollection|ShipmentPriceInterface[]
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
    public function hasPrice(ShipmentPriceInterface $price)
    {
        return $this->prices->contains($price);
    }

    /**
     * @inheritdoc
     */
    public function addPrice(ShipmentPriceInterface $price)
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
    public function removePrice(ShipmentPriceInterface $price)
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
