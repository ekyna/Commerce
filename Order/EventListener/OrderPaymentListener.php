<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\LockingHelperAwareTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderPaymentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentListener extends AbstractPaymentListener
{
    use LockingHelperAwareTrait;

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $payment = $this->getPaymentFromEvent($event);

        $this->preventForbiddenChange($payment);

        parent::onUpdate($event);
    }

    /**
     * @inheritDoc
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $payment = $this->getPaymentFromEvent($event);

        if ($this->lockingHelper->isLocked($payment)) {
            throw new Exception\IllegalOperationException(sprintf(
                'Payment %s is locked',
                $payment->getNumber()
            ));
        }

        parent::onDelete($event);
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        if ($event->getHard()) {
            return;
        }

        parent::onPreDelete($event);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleSaleContentChangeEvent(SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent(OrderEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function getPaymentFromEvent(ResourceEventInterface $event): PaymentInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderPaymentInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderPaymentInterface");
        }

        return $resource;
    }

    /**
     * Prevents some of the payment's properties to change.
     *
     * @param PaymentInterface $payment
     *
     * @throws Exception\IllegalOperationException
     */
    protected function preventForbiddenChange(PaymentInterface $payment): void
    {
        // Abort if not locked
        if (!$this->lockingHelper->isLocked($payment)) {
            return;
        }

        $cs = $this->persistenceHelper->getChangeSet($payment, 'state');

        // Allow state change to paid states.
        if (!empty($cs) && PaymentStates::isPaidState($cs[1])) {
            return;
        }

        // Allow only description change
        if (empty(array_diff(array_keys($cs), ['description']))) {
            return;
        }

        throw new Exception\IllegalOperationException(sprintf(
            'Payment %s is locked.',
            $payment->getNumber()
        ));
    }

    /**
     * @inheritdoc
     */
    protected function getSalePropertyPath(): string
    {
        return 'order';
    }
}
