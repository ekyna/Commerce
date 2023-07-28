<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Address;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
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
    public const CAPABILITY_SHIPMENT = 1;
    public const CAPABILITY_RETURN   = 2;
    public const CAPABILITY_PARCEL   = 4;
    public const CAPABILITY_RELAY    = 8;
    public const CAPABILITY_VIRTUAL  = 16;
    public const CAPABILITY_SYSTEM   = 32;

    public const REQUIREMENT_MOBILE = 1;

    public function getName(): string;

    public function getPlatform(): PlatformInterface;

    /**
     * Ships the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     *
     * @throws ShipmentGatewayException
     */
    public function ship(Shipment\ShipmentInterface $shipment): bool;

    /**
     * Cancels the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     *
     * @throws ShipmentGatewayException
     */
    public function cancel(Shipment\ShipmentInterface $shipment): bool;

    /**
     * Completes the given shipment.
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the operation succeed.
     *
     * @throws ShipmentGatewayException
     */
    public function complete(Shipment\ShipmentInterface $shipment): bool;

    /**
     * Tracks the given shipment or parcel.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     *
     * @return string The shipment's tracking url.
     *
     * @throws ShipmentGatewayException
     */
    public function track(Shipment\ShipmentDataInterface $shipment): ?string;

    /**
     * Proves the given shipment or parcel.
     *
     * @param Shipment\ShipmentDataInterface $shipment
     *
     * @return string The shipment's proof url.
     *
     * @throws ShipmentGatewayException
     */
    public function prove(Shipment\ShipmentDataInterface $shipment): ?string;

    /**
     * Prints the given shipment or parcel label.
     *
     * @return Shipment\ShipmentLabelInterface[] The shipment's labels.
     *
     * @throws ShipmentGatewayException
     */
    public function printLabel(Shipment\ShipmentDataInterface $shipment, array $types = null): array;

    /**
     * Builds the gateway data form.
     *
     * @param FormInterface $form
     *
     * @TODO break dependency with form component
     */
    public function buildForm(FormInterface $form): void;

    /**
     * Returns the relay point list for the given address and weight.
     *
     * @param Address $address The request address
     * @param Decimal $weight  In kilograms
     *
     * @return Model\ListRelayPointResponse The "list relay points" response
     *
     * @throws ShipmentGatewayException
     */
    public function listRelayPoints(Address $address, Decimal $weight): Model\ListRelayPointResponse;

    /**
     * Returns the relay point list for the given address and weight.
     *
     * @param string $number The relay point identifier
     *
     * @return Model\GetRelayPointResponse The "get relay point" response
     *
     * @throws ShipmentGatewayException
     */
    public function getRelayPoint(string $number): Model\GetRelayPointResponse;

    /**
     * Returns whether the gateway can execute the given action on the given shipment.
     *
     * @throws ShipmentGatewayException
     */
    public function can(Shipment\ShipmentInterface $shipment, string $action): bool;

    /**
     * Returns the gateway capabilities.
     */
    public function getActions(): array;

    /**
     * Returns the gateway capabilities.
     */
    public function getCapabilities(): ?int;

    /**
     * Returns the gateway requirements.
     */
    public function getRequirements(): ?int;

    /**
     * Returns whether this gateway supports the given capability.
     */
    public function supports(int $capability): bool;

    /**
     * Returns whether this gateway has the given requirement.
     */
    public function requires(int $requirement): bool;

    /**
     * Returns whether the gateway supports the given shipment.
     */
    public function supportShipment(Shipment\ShipmentDataInterface $shipment, bool $throw = true): bool;

    /**
     * Returns whether the given action is supported.
     */
    public function supportAction(string $action, bool $throw = true): bool;

    /**
     * Returns the maximum supported weight.
     *
     * @return Decimal|null
     */
    public function getMaxWeight(): ?Decimal;
}
