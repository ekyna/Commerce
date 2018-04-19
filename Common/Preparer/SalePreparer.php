<?php

namespace Ekyna\Component\Commerce\Common\Preparer;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;

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
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * Constructor.
     *
     * @param ResourceEventDispatcherInterface $eventDispatcher
     * @param StockPrioritizerInterface        $stockPrioritizer
     * @param ShipmentBuilderInterface         $shipmentBuilder
     * @param SaleFactoryInterface             $saleFactory
     */
    public function __construct(
        ResourceEventDispatcherInterface $eventDispatcher,
        StockPrioritizerInterface $stockPrioritizer,
        ShipmentBuilderInterface $shipmentBuilder,
        SaleFactoryInterface $saleFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->stockPrioritizer = $stockPrioritizer;
        $this->shipmentBuilder = $shipmentBuilder;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function prepare(SaleInterface $sale)
    {
        if (!$sale instanceof ShipmentSubjectInterface) {
            return null;
        }

        if (!$this->dispatchPrepareEvent($sale)) {
            return null;
        }

        if (!ShipmentStates::isPreparableState($sale->getShipmentState())) {
            return null;
        }

        if ($this->stockPrioritizer->canPrioritizeSale($sale)) {
            $this->stockPrioritizer->prioritizeSale($sale);
        }

        $shipment = $this->saleFactory->createShipmentForSale($sale);

        $sale->addShipment($shipment);

        $this->shipmentBuilder->build($shipment);

        if ($shipment->isEmpty()) {
            $sale->removeShipment($shipment);

            return null;
        }

        $shipment->setState(ShipmentStates::STATE_PREPARATION);

        return $shipment;
    }

    /**
     * @inheritdoc
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
            $this->eventDispatcher->dispatch(OrderEvents::PREPARE, $event);
        } catch (IllegalOperationException $e) {
            return false;
        }

        return true;
    }
}
