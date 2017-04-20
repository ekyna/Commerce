<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Gateway\Model\GetRelayPointResponse;
use Ekyna\Component\Commerce\Shipment\Gateway\Model\ListRelayPointResponse;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Form\FormInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway implements GatewayInterface
{
    use PersisterAwareTrait;
    use Shipment\AddressResolverAwareTrait;
    use Shipment\WeightCalculatorAwareTrait;

    protected PlatformInterface $platform;
    protected string            $name;
    protected array             $config;

    public function __construct(PlatformInterface $platform, string $name, array $config)
    {
        $this->platform = $platform;
        $this->name = $name;
        $this->config = $config;
    }

    public function getPlatform(): PlatformInterface
    {
        return $this->platform;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getActions(): array
    {
        return [
            GatewayActions::SHIP,
            GatewayActions::CANCEL,
            GatewayActions::COMPLETE,
        ];
    }

    public function getCapabilities(): ?int
    {
        return static::CAPABILITY_SHIPMENT;
    }

    public function getRequirements(): ?int
    {
        return null;
    }

    public function supports(int $capability): bool
    {
        return (bool)($capability & $this->getCapabilities());
    }

    public function requires(int $requirement): bool
    {
        return (bool)($requirement & $this->getRequirements());
    }

    public function ship(Shipment\ShipmentInterface $shipment): bool
    {
        $this->supportShipment($shipment);

        if ($shipment->isReturn()) {
            $validStates = [Shipment\ShipmentStates::STATE_PENDING, Shipment\ShipmentStates::STATE_RETURNED];
            $setState = Shipment\ShipmentStates::STATE_PENDING;
        } else {
            $validStates = [Shipment\ShipmentStates::STATE_SHIPPED];
            $setState = Shipment\ShipmentStates::STATE_SHIPPED;
        }

        if (in_array($shipment->getState(), $validStates, true)) {
            return false;
        }

        $shipment->setState($setState);

        $this->persister->persist($shipment);

        return true;
    }

    public function cancel(Shipment\ShipmentInterface $shipment): bool
    {
        $this->supportShipment($shipment);

        if ($shipment->getState() === Shipment\ShipmentStates::STATE_CANCELED) {
            return false;
        }

        $this->clearShipment($shipment);

        foreach ($shipment->getParcels() as $parcel) {
            $this->clearParcel($parcel);
        }

        $shipment->setState(Shipment\ShipmentStates::STATE_CANCELED);

        $this->persister->persist($shipment);

        return true;
    }

    public function complete(Shipment\ShipmentInterface $shipment): bool
    {
        $this->supportShipment($shipment);

        if (!$shipment->isReturn()) {
            return false;
        }

        if ($shipment->getState() !== Shipment\ShipmentStates::STATE_PENDING) {
            return false;
        }

        $shipment->setState(Shipment\ShipmentStates::STATE_RETURNED);

        $this->persister->persist($shipment);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormInterface $form): void
    {
    }

    public function track(Shipment\ShipmentDataInterface $shipment): ?string
    {
        $this->throwUnsupportedAction('track');
    }

    public function prove(Shipment\ShipmentDataInterface $shipment): ?string
    {
        $this->throwUnsupportedAction('prove');
    }

    public function printLabel(Shipment\ShipmentDataInterface $shipment, array $types = null): array
    {
        $this->throwUnsupportedAction('print');
    }

    public function listRelayPoints(Model\Address $address, Decimal $weight): ListRelayPointResponse
    {
        $this->throwUnsupportedAction('list relay points');
    }

    public function getRelayPoint(string $number): GetRelayPointResponse
    {
        $this->throwUnsupportedAction('get relay point');
    }

    public function can(Shipment\ShipmentInterface $shipment, string $action): bool
    {
        if (!($this->supportShipment($shipment, false) && $this->supportAction($action, false))) {
            return false;
        }

        switch ($action) {
            case GatewayActions::SHIP:
                if ($shipment->isReturn()) {
                    return !in_array($shipment->getState(), [
                        Shipment\ShipmentStates::STATE_PENDING,
                        Shipment\ShipmentStates::STATE_RETURNED,
                    ], true);
                }

                return $shipment->getState() !== Shipment\ShipmentStates::STATE_SHIPPED;

            case GatewayActions::CANCEL:
                return $shipment->getState() !== Shipment\ShipmentStates::STATE_CANCELED;

            case GatewayActions::COMPLETE:
                if (!$shipment->isReturn()) {
                    return false;
                }

                return $shipment->getState() === Shipment\ShipmentStates::STATE_PENDING;
        }

        return true;
    }

    public function supportShipment(Shipment\ShipmentDataInterface $shipment, bool $throw = true): bool
    {
        if ($shipment instanceof Shipment\ShipmentParcelInterface) {
            if (!(static::CAPABILITY_PARCEL & $this->getCapabilities())) {
                $this->throwUnsupportedShipment($shipment->getShipment(), 'Parcel given as argument.');
            }

            $shipment = $shipment->getShipment();
        }

        // Assert gateway name
        if ($shipment->getGatewayName() !== $this->getName()) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, 'Wrong gateway.');
            }

            return false;
        }

        // Assert shipment support
        if (!$shipment->isReturn() && !$this->supports(static::CAPABILITY_SHIPMENT)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, 'Shipments are not supported.');
            }

            return false;
        }

        // Assert return support
        if ($shipment->isReturn() && !$this->supports(static::CAPABILITY_RETURN)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, 'Returns are not supported.');
            }

            return false;
        }

        // Assert parcel support
        if ($shipment->hasParcels() && !$this->supports(static::CAPABILITY_PARCEL)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, 'Parcels are not supported.');
            }

            return false;
        }

        // TODO (?) Assert relay point support

        return true;
    }

    public function supportAction(string $action, bool $throw = true): bool
    {
        if (in_array($action, $this->getActions(), true)) {
            return true;
        }

        if ($throw) {
            $this->throwUnsupportedAction($action);
        }

        return false;
    }

    public function getMaxWeight(): ?Decimal
    {
        return null;
    }

    /**
     * Returns whether the shipment (or its parcels) has tracking number(s).
     *
     * @return bool Whether the given shipment has label data.
     */
    protected function hasTrackingNumber(Shipment\ShipmentInterface $shipment): bool
    {
        if ($shipment->hasParcels()) {
            foreach ($shipment->getParcels() as $parcel) {
                if (empty($parcel->getTrackingNumber())) {
                    return false;
                }
            }

            return true;
        }

        return !empty($shipment->getTrackingNumber());
    }

    /**
     * Returns whether the shipment (or its parcels) has label(s) data.
     *
     * @return bool Whether the given shipment has label data.
     */
    protected function hasLabelData(Shipment\ShipmentInterface $shipment): bool
    {
        if ($shipment->hasParcels()) {
            foreach ($shipment->getParcels() as $parcel) {
                if (!$parcel->hasLabels()) {
                    return false;
                }
            }

            return true;
        }

        return $shipment->hasLabels();
    }

    protected function createLabel(string $content, string $type, string $format, string $size): OrderShipmentLabel
    {
        $label = new OrderShipmentLabel(); // TODO use SaleFactory ?
        $label
            ->setContent($content)
            ->setType($type)
            ->setFormat($format)
            ->setSize($size);

        return $label;
    }

    /**
     * Clears the shipment data.
     */
    protected function clearShipment(Shipment\ShipmentInterface $shipment): bool
    {
        if (empty($shipment->getTrackingNumber()) && !$shipment->hasLabels()) {
            return false;
        }

        $shipment->setTrackingNumber(null);

        foreach ($shipment->getLabels() as $label) {
            $shipment->removeLabel($label);
        }

        return true;
    }

    /**
     * Clears the parcel data.
     */
    protected function clearParcel(Shipment\ShipmentParcelInterface $parcel): bool
    {
        if (empty($parcel->getTrackingNumber()) && !$parcel->hasLabels()) {
            return false;
        }

        $parcel->setTrackingNumber(null);

        foreach ($parcel->getLabels() as $label) {
            $parcel->removeLabel($label);
        }

        return true;
    }

    /**
     * Calculates the shipment's good value (for insurance).
     */
    protected function calculateGoodsValue(Shipment\ShipmentInterface $shipment): Decimal
    {
        $value = new Decimal(0);

        foreach ($shipment->getItems() as $item) {
            $saleItem = $item->getSaleItem();
            $value += $saleItem->getNetPrice() * $item->getQuantity();
        }

        return Money::round($value, $shipment->getSale()->getCurrency()->getCode());
    }

    /**
     * Throws an unsupported operation exception.
     *
     * @throws ShipmentGatewayException
     */
    protected function throwUnsupportedShipment(Shipment\ShipmentInterface $shipment, string $reason = null): void
    {
        throw new ShipmentGatewayException(sprintf(
            "Gateway '%s' does not support shipment '%s'. %s",
            $this->getName(), $shipment->getNumber(), $reason
        ));
    }

    /**
     * Throws an unsupported operation exception.
     *
     * @throws ShipmentGatewayException
     */
    protected function throwUnsupportedAction(string $operation, string $reason = null): void
    {
        throw new ShipmentGatewayException(sprintf(
            "The shipment gateway '%s' does not support '%s' operation. %s",
            $this->getName(), $operation, $reason
        ));
    }
}
