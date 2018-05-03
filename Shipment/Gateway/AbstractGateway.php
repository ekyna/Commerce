<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway;

use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentLabel;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Form\FormInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Component\Commerce\Shipment\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway implements GatewayInterface
{
    use Shipment\WeightCalculatorAwareTrait,
        Shipment\AddressResolverAwareTrait,
        PersisterAwareTrait;

    /**
     * @var PlatformInterface
     */
    protected $platform;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param PlatformInterface $platform
     * @param string            $name
     * @param array             $config
     */
    public function __construct(PlatformInterface $platform, $name, array $config)
    {
        $this->platform = $platform;
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return [
            GatewayActions::SHIP,
            GatewayActions::CANCEL,
            GatewayActions::COMPLETE,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities()
    {
        return static::CAPABILITY_SHIPMENT;
    }

    /**
     * @inheritDoc
     */
    public function getRequirements()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function supports(int $capability)
    {
        return (bool)($capability & $this->getCapabilities());
    }

    /**
     * @inheritDoc
     */
    public function requires(int $requirement)
    {
        return (bool)($requirement & $this->getRequirements());
    }

    /**
     * @inheritdoc
     */
    public function ship(Shipment\ShipmentInterface $shipment)
    {
        $this->supportShipment($shipment);

        if ($shipment->isReturn()) {
            $validStates = [Shipment\ShipmentStates::STATE_PENDING, Shipment\ShipmentStates::STATE_RETURNED];
            $setState = Shipment\ShipmentStates::STATE_PENDING;
        } else {
            $validStates = [Shipment\ShipmentStates::STATE_SHIPPED, Shipment\ShipmentStates::STATE_COMPLETED];
            $setState = Shipment\ShipmentStates::STATE_SHIPPED;
        }

        if (in_array($shipment->getState(), $validStates, true)) {
            return false;
        }

        $shipment->setState($setState);

        $this->persister->persist($shipment);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function cancel(Shipment\ShipmentInterface $shipment)
    {
        $this->supportShipment($shipment);

        $doPersist = $this->clearShipment($shipment);

        foreach ($shipment->getParcels() as $parcel) {
            $doPersist |= $this->clearParcel($parcel);
        }

        if ($shipment->isReturn()) {
            $fromStates = [Shipment\ShipmentStates::STATE_PENDING, Shipment\ShipmentStates::STATE_RETURNED];
            $toState = Shipment\ShipmentStates::STATE_CANCELED;
        } else {
            $fromStates = [Shipment\ShipmentStates::STATE_SHIPPED, Shipment\ShipmentStates::STATE_COMPLETED];
            $toState = Shipment\ShipmentStates::STATE_CANCELED;
        }

        if (in_array($shipment->getState(), $fromStates, true)) {
            $shipment->setState($toState);
            $doPersist = true;
        }

        if ($doPersist) {
            $this->persister->persist($shipment);
        }

        return $doPersist;
    }

    /**
     * @inheritdoc
     */
    public function complete(Shipment\ShipmentInterface $shipment)
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
    public function buildForm(FormInterface $form)
    {

    }

    /**
     * @inheritdoc
     */
    public function track(Shipment\ShipmentDataInterface $shipment)
    {
        $this->throwUnsupportedAction('track');
    }

    /**
     * @inheritdoc
     */
    public function prove(Shipment\ShipmentDataInterface $shipment)
    {
        $this->throwUnsupportedAction('prove');
    }

    /**
     * @inheritdoc
     */
    public function printLabel(Shipment\ShipmentDataInterface $shipment, array $types = null)
    {
        $this->throwUnsupportedAction('print');
    }

    /**
     * @inheritdoc
     */
    public function listRelayPoints(Model\Address $address, float $weight)
    {
        $this->throwUnsupportedAction('list relay points');
    }

    /**
     * @inheritdoc
     */
    public function getRelayPoint(string $number)
    {
        $this->throwUnsupportedAction('get relay point');
    }

    /**
     * @inheritdoc
     */
    public function can(Shipment\ShipmentInterface $shipment, $action)
    {
        if (!($this->supportShipment($shipment, false) && $this->supportAction($action, false))) {
            return false;
        }

        switch ($action) {
            case GatewayActions::SHIP:
                // TODO (?) If supports tracking and has tracking number -> return false
                /*if ($this->hasTrackingNumber($shipment)) {
                    return false;
                }*/

                if ($shipment->isReturn()) {
                    return !in_array($shipment->getState(), [
                        Shipment\ShipmentStates::STATE_PENDING,
                        Shipment\ShipmentStates::STATE_COMPLETED
                    ], true);
                }

                return $shipment->getState() !== Shipment\ShipmentStates::STATE_COMPLETED;

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

    /**
     * @inheritdoc
     */
    public function supportShipment(Shipment\ShipmentDataInterface $shipment, $throw = true)
    {
        if ($shipment instanceof Shipment\ShipmentParcelInterface) {
            if (!(static::CAPABILITY_PARCEL & $this->getCapabilities())) {
                $this->throwUnsupportedShipment($shipment->getShipment(), "Parcel given as argument.");
            }

            $shipment = $shipment->getShipment();
        }

        // Assert gateway name
        if ($shipment->getGatewayName() !== $this->getName()) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, "Wrong gateway.");
            }

            return false;
        }

        // Assert shipment support
        if (!$shipment->isReturn() && !$this->supports(static::CAPABILITY_SHIPMENT)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, "Shipments are not supported.");
            }

            return false;
        }

        // Assert return support
        if ($shipment->isReturn() && !$this->supports(static::CAPABILITY_RETURN)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, "Returns are not supported.");
            }

            return false;
        }

        // Assert parcel support
        if ($shipment->hasParcels() && !$this->supports(static::CAPABILITY_PARCEL)) {
            if ($throw) {
                $this->throwUnsupportedShipment($shipment, "Parcels are not supported.");
            }

            return false;
        }

        // TODO (?) Assert relay point support

        return true;
    }

    /**
     * @inheritdoc
     */
    public function supportAction(string $action, $throw = true)
    {
        if (in_array($action, $this->getActions(), true)) {
            return true;
        }

        if ($throw) {
            $this->throwUnsupportedAction($action);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getMaxWeight()
    {
        return null;
    }

    /**
     * Returns whether the shipment (or its parcels) has tracking number(s).
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the given shipment has label data.
     */
    protected function hasTrackingNumber(Shipment\ShipmentInterface $shipment)
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
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool Whether the given shipment has label data.
     */
    protected function hasLabelData(Shipment\ShipmentInterface $shipment)
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

    /**
     * Creates the shipment label.
     *
     * @param string $content
     * @param string $type
     * @param string $format
     * @param string $size
     *
     * @return OrderShipmentLabel
     */
    protected function createLabel($content, $type, $format, $size)
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
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return bool
     */
    protected function clearShipment(Shipment\ShipmentInterface $shipment)
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
     *
     * @param Shipment\ShipmentParcelInterface $parcel
     *
     * @return bool
     */
    protected function clearParcel(Shipment\ShipmentParcelInterface $parcel)
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
     *
     * @param Shipment\ShipmentInterface $shipment
     *
     * @return float
     */
    protected function calculateGoodsValue(Shipment\ShipmentInterface $shipment)
    {
        $value = 0;

        foreach ($shipment->getItems() as $item) {
            $saleItem = $item->getSaleItem();
            $value += $saleItem->getNetPrice() * $item->getQuantity();
        }

        return round($value, 2); // TODO Convert/Round regarding to gateway and sale currencies
    }

    /**
     * Throws an unsupported operation exception.
     *
     * @param Shipment\ShipmentInterface $shipment
     * @param string                     $reason
     *
     * @throws ShipmentGatewayException
     */
    protected function throwUnsupportedShipment(Shipment\ShipmentInterface $shipment, $reason = null)
    {
        throw new ShipmentGatewayException(sprintf(
            "Gateway '%s' does not support shipment '%s'. %s",
            $this->getName(), $shipment->getNumber(), $reason
        ));
    }

    /**
     * Throws an unsupported operation exception.
     *
     * @param string $operation
     * @param string $reason
     *
     * @throws ShipmentGatewayException
     */
    protected function throwUnsupportedAction($operation, $reason = null)
    {
        throw new ShipmentGatewayException(sprintf(
            "The shipment gateway '%s' does not support '%s' operation. %s",
            $this->getName(), $operation, $reason
        ));
    }
}
