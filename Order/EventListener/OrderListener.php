<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use DateTime;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

use function in_array;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderListener extends AbstractSaleListener
{
    protected readonly StockUnitAssignerInterface      $stockAssigner;
    protected readonly OrderRepositoryInterface        $orderRepository;
    protected readonly CouponRepositoryInterface       $couponRepository;
    protected readonly InvoicePaymentResolverInterface $invoicePaymentResolver;
    protected readonly OrderUpdaterInterface           $orderUpdater;

    public function setStockAssigner(StockUnitAssignerInterface $assigner): void
    {
        $this->stockAssigner = $assigner;
    }

    public function setOrderRepository(OrderRepositoryInterface $repository): void
    {
        $this->orderRepository = $repository;
    }

    public function setCouponRepository(CouponRepositoryInterface $repository): void
    {
        $this->couponRepository = $repository;
    }

    public function setInvoicePaymentResolver(InvoicePaymentResolverInterface $resolver): void
    {
        $this->invoicePaymentResolver = $resolver;
    }

    public function setOrderUpdater(OrderUpdaterInterface $orderUpdater): void
    {
        $this->orderUpdater = $orderUpdater;
    }

    /**
     * Prepare event handler.
     */
    public function onPrepare(ResourceEventInterface $event): void
    {
        $order = $this->getSaleFromEvent($event);

        if (!OrderStates::isStockableState($order->getState())) {
            throw new IllegalOperationException(
                'Order is not ready for shipment preparation'
            );
        }
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        parent::onPreDelete($event);

        $order = $this->getSaleFromEvent($event);

        // Stop if order has invoices or shipments
        if ($order->hasInvoices() || $order->hasShipments()) {
            throw new IllegalOperationException(
                "Order with invoices or shipments can't be deleted."
            );
        }
    }

    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $sale = $this->getSaleFromEvent($event);

        if ($sale->isSample() && ($sale->hasPayments() || $sale->hasInvoices())) {
            throw new IllegalOperationException( // TODO Translation
                "Order with payments or invoices can't be turned into sample order."
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleInsert(SaleInterface $sale): bool
    {
        $changed = $this->fixCustomers($sale);

        $changed = parent::handleInsert($sale) || $changed;

        $changed = $this->setIsFirst($sale) || $changed;

        $this->handleCouponChange($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = $this->fixCustomers($sale);

        $changed = parent::handleUpdate($sale) || $changed;

        $changed = $this->handleReleasedChange($sale) || $changed;

        $this->handleCouponChange($sale);

        return $changed;
    }

    /**
     * Handles the released flag change.
     */
    protected function handleReleasedChange(OrderInterface $order): bool
    {
        if ($this->persistenceHelper->isChanged($order, 'sample')) {
            if ($order->isReleased() && !$order->isSample()) {
                throw new IllegalOperationException("Can't turn 'sample' into false if order is released.");
            }
        }

        if (!$this->persistenceHelper->isChanged($order, 'released')) {
            return false;
        }

        // Orders that are not samples can't be released.
        if (!$order->isSample() && $order->isReleased()) {
            $order->setReleased(false);

            return true;
        }

        if (!OrderStates::isStockableState($order->getState())) {
            return false;
        }

        foreach ($order->getItems() as $item) {
            $this->applySaleItemRecursively($item);
        }

        return false;
    }

    /**
     * Sets whether this order is the customer's first one.
     *
     * @return bool Whether the order has been changed.
     */
    protected function setIsFirst(OrderInterface $order): bool
    {
        if ($customer = $order->getCustomer()) {
            if ($customer->hasParent()) {
                $customer = $customer->getParent();
            }
        }

        if ($customer && $customer->getId()) {
            $first = !$this->orderRepository->existsForCustomer($customer);
        } else {
            $first = !$this->orderRepository->existsForEmail($order->getEmail());
        }

        if ($first != $order->isFirst()) {
            $order->setFirst($first);

            return true;
        }

        return false;
    }

    /**
     * Changes the customer and origin customer regarding their hierarchy.
     */
    protected function fixCustomers(OrderInterface $order): bool
    {
        $changed = false;

        $originCustomer = $order->getOriginCustomer();
        $customer = $order->getCustomer();

        if (null === $customer) {
            if ($originCustomer && $originCustomer->hasParent()) {
                $order->setCustomer($originCustomer->getParent());

                $changed = true;
            }
        } elseif ($customer->hasParent()) {
            $order->setCustomer($customer->getParent());

            if (null === $order->getOriginCustomer()) {
                $order->setOriginCustomer($customer);
            }

            $changed = true;
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($order, false);
        }

        return $changed;
    }

    /**
     * Handles coupon change.
     */
    protected function handleCouponChange(SaleInterface $sale): void
    {
        if (empty($cs = $this->persistenceHelper->getChangeSet($sale, 'coupon'))) {
            return;
        }

        [$old, $new] = $cs;

        if ($old === $new) {
            return;
        }

        if ($old) {
            $coupon = $old;
            $modifier = -1;
        } else {
            $coupon = $new;
            $modifier = +1;
        }

        /** @var CouponInterface $coupon */

        $usage = $this->orderRepository->getCouponUsage($coupon) + $modifier;

        $coupon->setUsage($usage);

        $this->persistenceHelper->persistAndRecompute($coupon, false);
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleStateChange(SaleInterface $sale): void
    {
        parent::handleStateChange($sale);

        $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

        if (empty($stateCs)) {
            return;
        }

        if (OrderStates::hasChangedToStockable($stateCs)) {
            // Order state has changed from non stockable to stockable
            foreach ($sale->getItems() as $item) {
                $this->assignSaleItemRecursively($item);
            }

            return;
        }

        if (OrderStates::hasChangedFromStockable($stateCs)) {
            // Order state has changed from stockable to non stockable
            foreach ($sale->getItems() as $item) {
                $this->detachSaleItemRecursively($item);
            }
            // We don't need to handle invoices as they are detached with sale items.
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleContentChange(SaleInterface $sale): void
    {
        $this->updateInvoicePaidTotal($sale);

        parent::handleContentChange($sale);

        $this->orderUpdater->updateMargin($sale);

        $this->orderUpdater->updateItemsCount($sale);
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function updateDueDates(SaleInterface $sale): bool
    {
        /** @var InvoiceInterface $invoice */
        foreach ($sale->getInvoices() as $invoice) {
            $resolved = $this->dueDateResolver->resolveInvoiceDueDate($invoice);

            if (DateUtil::equals($resolved, $invoice->getDueDate())) {
                continue;
            }

            $invoice->setDueDate($resolved);

            $this->persistenceHelper->persistAndRecompute($invoice, false);
        }

        return parent::updateDueDates($sale);
    }

    /**
     * Updates the invoices paid amounts.
     *
     * @param OrderInterface $sale
     */
    protected function updateInvoicePaidTotal(OrderInterface $sale): void
    {
        /** @var InvoiceInterface $invoice */
        foreach ($sale->getInvoices()->toArray() as $invoice) {
            $changed = false;

            $total = $this->invoicePaymentResolver->getPaidTotal($invoice);
            if (!$invoice->getPaidTotal()->equals($total)) {
                $invoice->setPaidTotal($total);
                $changed = true;
            }

            $total = $this->invoicePaymentResolver->getRealPaidTotal($invoice);
            if (!$invoice->getRealPaidTotal()->equals($total)) {
                $invoice->setRealPaidTotal($total);
                $changed = true;
            }

            if ($changed) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);
            }
        }
    }

    protected function isDiscountUpdateNeeded(SaleInterface $sale): bool
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isDiscountUpdateNeeded($sale);
    }

    protected function isTaxationUpdateNeeded(SaleInterface $sale): bool
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isTaxationUpdateNeeded($sale);
    }

    protected function isShipmentTaxationUpdateNeeded(SaleInterface $sale): bool
    {
        if ($this->persistenceHelper->isChanged($sale, 'sample')) {
            return true;
        }

        return parent::isShipmentTaxationUpdateNeeded($sale);
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function updateVatDisplayMode(SaleInterface $sale): bool
    {
        // Vat display mode must not change if order has shipments or invoices.
        if ($sale->hasShipments() || $sale->hasInvoices()) {
            return false;
        }

        return parent::updateVatDisplayMode($sale);
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function updateState(SaleInterface $sale): bool
    {
        if (!parent::updateState($sale)) {
            return false;
        }

        if (in_array($state = $sale->getState(), OrderStates::getStockableStates(), true)) {
            if ($state !== OrderStates::STATE_COMPLETED) {
                $sale->setCompletedAt(null);
            } elseif (null === $sale->getCompletedAt()) {
                $sale->setCompletedAt(new DateTime());
            }

            if (null === $sale->getAcceptedAt()) {
                $sale->setAcceptedAt(new DateTime());
            }
        } else {
            $sale->setAcceptedAt(null);
            $sale->setCompletedAt(null);
        }

        return true;
    }


    /**
     * Assigns the sale item to stock units recursively.
     */
    protected function assignSaleItemRecursively(SaleItemInterface $item): void
    {
        $this->stockAssigner->assignSaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->assignSaleItemRecursively($child);
        }
    }

    /**
     * Applies the sale item to stock units recursively.
     */
    protected function applySaleItemRecursively(SaleItemInterface $item): void
    {
        $this->stockAssigner->applySaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->applySaleItemRecursively($child);
        }
    }

    /**
     * Detaches the sale item from stock units recursively.
     */
    protected function detachSaleItemRecursively(SaleItemInterface $item): void
    {
        $this->stockAssigner->detachSaleItem($item);

        foreach ($item->getChildren() as $child) {
            $this->detachSaleItemRecursively($child);
        }
    }

    /**
     * @inheritDoc
     *
     * @return OrderInterface
     */
    protected function getSaleFromEvent(ResourceEventInterface $event): SaleInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInterface) {
            throw new UnexpectedTypeException($resource, OrderInterface::class);
        }

        return $resource;
    }

    protected function scheduleContentChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::CONTENT_CHANGE);
    }

    protected function scheduleStateChangeEvent(SaleInterface $sale): void
    {
        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::STATE_CHANGE);
    }
}
