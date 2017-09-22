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
     * Returns the factory name.
     *
     * @return string
     */
    public function getFactoryName();

    /**
     * Sets the factory name.
     *
     * @param string $name
     *
     * @return $this|ShipmentMethodInterface
     */
    public function setFactoryName($name);

    /**
     * Returns the gateway name.
     *
     * @return array
     */
    public function getGatewayName();

    /**
     * Returns the gatewayConfig.
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
    public function setGatewayConfig(array $config);

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
