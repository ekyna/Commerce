<?php

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Calculator\CalculatorInterface;
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
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     * @param CalculatorInterface      $calculator
     * @param StateResolverInterface   $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        CalculatorInterface $calculator,
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

        // Generate number and key
        $changed = $this->generateNumber($order);

        $changed |= $this->updateState($order);

        $changed |= $this->updateTotal($order);

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

        // Generate number and key
        $changed = $this->generateNumber($order);

        // Update state
        $changed |= $this->updateState($order);

        // Update state
        $changed |= $this->updateTotal($order);

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
                    $this->deleteSupplierOrderItemStockUnit($item);
                }
            } // Else if order state's has changed to a stockable state
            elseif (SupplierOrderStates::hasChangedToStockable($stateCs)) {
                // Create stock unit (if not exists) for each supplier order items.
                foreach ($order->getItems() as $item) {
                    $this->createSupplierOrderItemStockUnit($item);
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

        $changed |= $this->updateTotal($order);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order);
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $order = $this->getSupplierOrderFromEvent($event);

        $this->assertDeletable($order);
    }

    /**
     * Generates the number.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order has been generated or not.
     */
    protected function generateNumber(SupplierOrderInterface $order)
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
     * @return bool Whether the supplier order has been changed or not.
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
     * Updates the payment total.
     *
     * @param SupplierOrderInterface $order
     *
     * @return bool Whether the supplier order has been changed or not.
     */
    protected function updateTotal(SupplierOrderInterface $order)
    {
        $total = $this->calculator->calculatePaymentTotal($order);

        if ($total != $order->getPaymentTotal()) {
            $order->setPaymentTotal($total);

            return true;
        }

        return false;
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
