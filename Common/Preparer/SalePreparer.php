<?php

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;

use function is_null;

/**
 * Class SalePreparer
 * @package Ekyna\Component\Commerce\Common\Preparer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePreparer implements SalePreparerInterface
{
    /**
     * @var ResourceEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var StockPrioritizerInterface
     */
    private $stockPrioritizer;

    /**
     * @var ShipmentBuilderInterface
     */
    private $shipmentBuilder;

    /**
     * @var FactoryHelperInterface
     */
    private $factoryHelper;

    public function __construct(
        ResourceEventDispatcherInterface $eventDispatcher,
        StockPrioritizerInterface        $stockPrioritizer,
        ShipmentBuilderInterface         $shipmentBuilder,
        FactoryHelperInterface           $factoryHelper
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->stockPrioritizer = $stockPrioritizer;
        $this->shipmentBuilder = $shipmentBuilder;
        $this->factoryHelper = $factoryHelper;
    }

    /**
     * @inheritDoc
     */
    public function prepare(SaleInterface $sale)
    {
        if (!$sale instanceof ShipmentSubjectInterface) {
            return null;
        }

        // TODO Abort if not stockable

        if (!$this->dispatchPrepareEvent($sale)) {
            return null;
        }

        if (!ShipmentStates::isPreparableState($sale->getShipmentState())) {
            return null;
        }

        if ($this->stockPrioritizer->canPrioritizeSale($sale)) {
            $this->stockPrioritizer->prioritizeSale($sale);
        }

        $shipment = $this->factoryHelper->createShipmentForSale($sale);

        $sale->addShipment($shipment);

        $this->shipmentBuilder->build($shipment);

        $this->purge($shipment);

        if ($shipment->isEmpty()) {
            $sale->removeShipment($shipment);

            return null;
        }

        $shipment->setState(ShipmentStates::STATE_PREPARATION);

        return $shipment;
    }

    /**
     * Purges the shipment by removing items which are not available.
     *
     * @param ShipmentInterface $shipment
     */
    protected function purge(ShipmentInterface $shipment)
    {
        foreach ($shipment->getItems() as $item) {
            if (is_null($available = $item->getAvailable()) || $available->isZero()) {
                $shipment->removeItem($item);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function abort(SaleInterface $sale)
    {
        if (!$sale instanceof ShipmentSubjectInterface) {
            return null;
        }

        foreach ($sale->getShipments() as $shipment) {
            if ($shipment->getState() === ShipmentStates::STATE_PREPARATION) {
                return $shipment;
            }
        }

        return null;
    }

    /**
     * Dispatches the sale prepare event.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function dispatchPrepareEvent(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInterface::class);
        }

        $event = $this->eventDispatcher->createResourceEvent($sale);

        try {
            /** @noinspection PhpParamsInspection */
            $this->eventDispatcher->dispatch($event, OrderEvents::PREPARE);
        } catch (IllegalOperationException $e) {
            return false;
        }

        return true;
    }
}
