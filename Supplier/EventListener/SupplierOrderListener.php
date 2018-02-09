<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class SupplierOrderListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderListener extends AbstractListener
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var SupplierOrderCalculatorInterface
     */
    protected $calculator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface         $numberGenerator
     * @param SupplierOrderCalculatorInterface $calculator
     * @param StateResolverInterface           $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        SupplierOrderCalculatorInterface $calculator,
        StateResolverInterface $stateResolver
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->calculator = $calculator;
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->updateNumber($order);

        $changed |= $this->updateState($order);

        $changed |= $this->updateTotals($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->updateNumber($order);

        $changed |= $this->updateState($order);

        $changed |= $this->updateTotals($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }

        // Deletable <=> Stockable state change case.
        if ($this->persistenceHelper->isChanged($order, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($order, 'state');

            // If order's state has changed to a non stockable state
            if (SupplierOrderStates::hasChangedFromStockable($stateCs)) {
                // Delete stock unit (if exists) for each supplier order items.
                foreach ($order->getItems() as $item) {
                    $this->stockUnitLinker->unlinkItem($item);
                    //$this->deleteSupplierOrderItemStockUnit($item);
                }
            } // Else if order state's has changed to a stockable state
            elseif (SupplierOrderStates::hasChangedToStockable($stateCs)) {
                // Create stock unit (if not exists) for each supplier order items.
                foreach ($order->getItems() as $item) {
                    $this->stockUnitLinker->linkItem($item);
                    //$this->createSupplierOrderItemStockUnit($item);
                }
            }
        }

        // If order's estimated date of arrival has changed and order's state is stockable
        if (
            $this->persistenceHelper->isChanged($order, 'estimatedDateOfArrival')
            && SupplierOrderStates::isStockableState($order->getState())
        ) {
            // Update stock units estimated date of arrival
            foreach ($order->getItems() as $item) {
                $this->stockUnitUpdater
                    ->updateEstimatedDateOfArrival($item->getStockUnit(), $order->getEstimatedDateOfArrival());
            }
        }
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $changed = $this->updateState($order);

        $changed |= $this->updateTotals($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        if (null !== $supplier = $order->getSupplier()) {
            if ($order->getCurrency() !== $supplier->getCurrency()) {
                $order->setCurrency($supplier->getCurrency());
            }
            if (null === $order->getCarrier()) {
                $order->setCarrier($supplier->getCarrier());
            }
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $this->assertDeletable($order);
    }

    /**
     * Updates the number.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order number has been changed.
     */
    protected function updateNumber(SupplierOrderInterface $order)
    {
        if (0 == strlen($order->getNumber())) {
            $this->numberGenerator->generate($order);

            return true;
        }

        return false;
    }

    /**
     * Updates the state.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order has been changed.
     */
    protected function updateState(SupplierOrderInterface $order)
    {
        $changed = $this->stateResolver->resolve($order);

        // If order state is 'completed' and 'competed at' date is not set
        if ($order->getState() === SupplierOrderStates::STATE_COMPLETED
            && null === $order->getCompletedAt()
        ) {
            // Set the 'completed at' date
            $order->setCompletedAt(new \DateTime());
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the payment and forwarder totals.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether or not the supplier order has been changed.
     */
    protected function updateTotals(SupplierOrderInterface $order)
    {
        $changed = false;

        $forwarder = $this->calculator->calculatePaymentTotal($order);
        if ($forwarder != $order->getPaymentTotal()) {
            $order->setPaymentTotal($forwarder);

            $changed = true;
        }

        $forwarder = $this->calculator->calculateForwarderTotal($order);
        if ($forwarder != $order->getForwarderTotal()) {
            $order->setForwarderTotal($forwarder);

            $changed = true;
        }

        return $changed;
    }

    /**
     * Returns the supplier order from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SupplierOrderInterface
     * @throws InvalidArgumentException
     */
    protected function getSupplierOrderFromEvent(ResourceEventInterface $event)
    {
        $order = $event->getResource();

        if (!$order instanceof SupplierOrderInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierOrderInterface.");
        }

        return $order;
    }
}
