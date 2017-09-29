<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\MethodInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;

/**
 * Interface ShipmentMethodInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodInterface extends MethodInterface, TaxableInterface
{
    /**
     * Returns the platform name.
     *
     * @return string
     */
    public function getPlatformName();

    /**
     * Sets the platform name.
     *
     * @param string $name
     *
     * @return $this|ShipmentMethodInterface
     */
    public function setPlatformName($name);

    /**
     * Returns the gateway name.
     *
     * @return string
     */
    public function getGatewayName();

    /**
     * Sets the gateway name.
     *
     * @param string $name
     *
     * @return $this|ShipmentMethodInterface
     */
    public function setGatewayName($name);

    /**
     * Returns the gateway array.
     *
     * @return array
     */
    public function getGatewayConfig();

    /**
     * Sets the gateway config.
     *
     * @param array $config
     *
     * @return $this|ShipmentMethodInterface
     */
    public function setGatewayConfig(array $config = null);

    /**
     * Returns the prices.
     *
     * @return ArrayCollection|ShipmentPriceInterface[]
     */
    public function getPrices();

    /**
     * Returns whether or not the zone has at least one price.
     *
     * @return bool
     */
    public function hasPrices();

    /**
     * Returns whether or not the zone has the given price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return bool
     */
    public function hasPrice(ShipmentPriceInterface $price);

    /**
     * Adds the price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return $this|ShipmentMethodInterface
     */
    public function addPrice(ShipmentPriceInterface $price);

    /**
     * Removes the price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return $this|ShipmentMethodInterface
     */
    public function removePrice(ShipmentPriceInterface $price);
}
