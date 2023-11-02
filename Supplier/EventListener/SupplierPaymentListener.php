<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\EventListener;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierPaymentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function array_key_exists;

/**
 * Class SupplierPaymentListener
 * @package Ekyna\Component\Commerce\Supplier\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierPaymentListener
{
    public function __construct(
        protected readonly CurrencyConverterInterface $currencyConverter,
        protected readonly PersistenceHelperInterface $persistenceHelper,
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $payment = $this->getSupplierPaymentFromEvent($event);

        $this->updateExchangeRate($payment);

        $this->scheduleSupplierOrderContentChangeEvent($payment->getOrder());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $payment = $this->getSupplierPaymentFromEvent($event);

        $this->updateExchangeRate($payment);

        $this->scheduleSupplierOrderContentChangeEvent($payment->getOrder());
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $payment = $this->getSupplierPaymentFromEvent($event);

        if (null === $order = $payment->getOrder()) {
            $changeSet = $this->persistenceHelper->getChangeSet($payment);
            if (array_key_exists('order', $changeSet)) {
                $order = $changeSet['order'][0];
            }
        }
        if (null === $order) {
            throw new Exception\RuntimeException('Failed to retrieve supplier order.');
        }

        // Clear association
        $payment->setOrder(null);

        // Trigger the supplier order update
        if (!$this->persistenceHelper->isScheduledForRemove($order)) {
            $this->scheduleSupplierOrderContentChangeEvent($order);
        }
    }

    private function updateExchangeRate(SupplierPaymentInterface $payment): void
    {
        if (PaymentStates::STATE_CAPTURED !== $payment->getState()) {
            if (null !== $payment->getExchangeRate()) {
                $payment
                    ->setExchangeDate(null)
                    ->setExchangeRate(null);

                $this->persistenceHelper->persistAndRecompute($payment, false);
            }

            return;
        }

        if (!$this->currencyConverter->setSubjectExchangeRate($payment)) {
            return;
        }

        $this->persistenceHelper->persistAndRecompute($payment, false);
    }

    /**
     * Schedules the supplier order content change event.
     */
    protected function scheduleSupplierOrderContentChangeEvent(SupplierOrderInterface $order): void
    {
        $this->persistenceHelper->scheduleEvent($order, SupplierOrderEvents::CONTENT_CHANGE);
    }

    /**
     * Returns the supplier payment item from the event.
     */
    protected function getSupplierPaymentFromEvent(ResourceEventInterface $event): SupplierPaymentInterface
    {
        $payment = $event->getResource();

        if (!$payment instanceof SupplierPaymentInterface) {
            throw new Exception\UnexpectedTypeException($payment, SupplierPaymentInterface::class);
        }

        return $payment;
    }
}
