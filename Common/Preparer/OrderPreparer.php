<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizeCheckerInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizerInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;

use function is_null;

/**
 * Class SalePreparer
 * @package Ekyna\Component\Commerce\Common\Preparer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPreparer implements OrderPreparerInterface
{
    public function __construct(
        private readonly ResourceEventDispatcherInterface $eventDispatcher,
        private readonly OrderPrioritizeCheckerInterface  $prioritizeChecker,
        private readonly OrderPrioritizerInterface        $stockPrioritizer,
        private readonly ShipmentBuilderInterface         $shipmentBuilder,
        private readonly FactoryHelperInterface           $factoryHelper
    ) {
    }

    public function prepare(OrderInterface $order): ?ShipmentInterface
    {
        if (!$this->dispatchPrepareEvent($order)) {
            return null;
        }

        if (!ShipmentStates::isPreparableState($order->getShipmentState())) {
            return null;
        }

        if ($this->prioritizeChecker->check($order)) {
            $this->stockPrioritizer->prioritize($order);
        }

        $shipment = $this->factoryHelper->createShipmentForSale($order);

        $order->addShipment($shipment);

        $this->shipmentBuilder->build($shipment);

        $this->purge($shipment);

        if ($shipment->isEmpty()) {
            $order->removeShipment($shipment);

            return null;
        }

        $shipment->setState(ShipmentStates::STATE_PREPARATION);

        return $shipment;
    }

    /**
     * Purges the shipment by removing items which are not available.
     */
    protected function purge(ShipmentInterface $shipment): void
    {
        foreach ($shipment->getItems() as $item) {
            if (is_null($available = $item->getAvailability()) || $available->getAssigned()->isZero()) {
                $shipment->removeItem($item);
            }
        }
    }

    public function abort(OrderInterface $order): ?ShipmentInterface
    {
        foreach ($order->getShipments() as $shipment) {
            if ($shipment->getState() === ShipmentStates::STATE_PREPARATION) {
                return $shipment;
            }
        }

        return null;
    }

    /**
     * Dispatches the sale prepare event.
     */
    protected function dispatchPrepareEvent(SaleInterface $sale): bool
    {
        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        $event = $this->eventDispatcher->createResourceEvent($sale);

        try {
            $this->eventDispatcher->dispatch($event, OrderEvents::PREPARE);
        } catch (IllegalOperationException $e) {
            return false;
        }

        return true;
    }
}
