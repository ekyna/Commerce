<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Form\FormInterface;

/**
 * Interface GatewayInterface
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GatewayInterface extends
    Shipment\WeightCalculatorAwareInterface,
    Shipment\AddressResolverAwareInterface,
    PersisterAwareInterface
{
    const CAPABILITY_SHIPMENT = 1;
    const CAPABILITY_RETURN   = 2;
    const CAPABILITY_PARCEL   = 4;
    const CAPABILITY_RELAY    = 8;

    const REQUIREMENT_MOBILE  = 1;


    /**
     * Returns the gateway name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the platform.
     *
     * @return PlatformInterface
     */
    public function getPlatform();

    /**
     * Ships the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     */
    public function ship(Shipment\ShipmentInterface $shipment);

    /**
     * Cancels the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     */
    public function cancel(Shipment\ShipmentInterface $shipment);

    /**
     * Completes the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     */
    public function complete(Shipment\ShipmentInterface $shipment);

    /**
     * Tracks the given shipment or parcel.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     *
     * @return string The shipment's tracking url.
     */
    public function track(Shipment\ShipmentDataInterface $shipment);

    /**
     * Proves the given shipment or parcel.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     *
     * @return string The shipment's proof url.
     */
    public function prove(Shipment\ShipmentDataInterface $shipment);

    /**
     * Prints the given shipment or parcel label.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     * @param array                          $types
     *
     * @return Shipment\ShipmentLabelInterface[] The shipment's labels.
     */
    public function printLabel(Shipment\ShipmentDataInterface $shipment, array $types = null);

    /**
     * Builds the gateway data form.
     *
     * @param FormInterface $form
     *
     * @TODO break dependency with form component
     */
    public function buildForm(FormInterface $form);

    /**
     * Returns the relay point list for the given address and weight.
     *
     * @param Model\Address $address The request address
     * @param float         $weight  In kilograms
     *
     * @return Model\ListRelayPointResponse The "list relay points" response
     */
    public function listRelayPoints(Model\Address $address, float $weight);

    /**
     * Returns the relay point list for the given address and weight.
     *
     * @param string $number The relay point identifier
     *
     * @return Model\GetRelayPointResponse The "get relay point" response
     */
    public function getRelayPoint(string $number);

    /**
     * Returns whether the gateway can execute the given action on the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     * @param string                     $action
     *
     * @return bool
     */
    public function can(Shipment\ShipmentInterface $shipment, $action);

    /**
     * Returns the gateway capabilities.
     *
     * @return array
     */
    public function getActions();

    /**
     * Returns the gateway capabilities.
     *
     * @return int
     */
    public function getCapabilities();

    /**
     * Returns the gateway requirements.
     *
     * @return int
     */
    public function getRequirements();

    /**
     * Returns whether this gateway supports the given capability.
     *
     * @param int $capability
     *
     * @return bool
     */
    public function supports(int $capability);

    /**
     * Returns whether this gateway has the given requirement.
     *
     * @param int $requirement
     *
     * @return bool
     */
    public function requires(int $requirement);

    /**
     * Returns whether the gateway supports the given shipment.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     * @param bool                           $throw
     *
     * @return bool
     */
    public function supportShipment(Shipment\ShipmentDataInterface $shipment, $throw = true);

    /**
     * Returns whether the given action is supported.
     *
     * @param string $action
     * @param bool   $throw
     *
     * @return bool
     */
    public function supportAction(string $action, $throw = true);

    /**
     * Returns the maximum supported weight.
     *
     * @return float|null
     */
    public function getMaxWeight();
}
