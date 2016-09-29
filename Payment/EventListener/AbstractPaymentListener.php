<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractPaymentListener
 * @package Ekyna\Component\Commerce\Payment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setNumberGenerator(NumberGeneratorInterface $generator)
    {
        $this->numberGenerator = $generator;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        // Generate number and key
        $changed = $this->generateNumber($payment);

        // TODO Timestampable behavior/listener
        $payment
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        $sale = $payment->getSale();
        $sale->addPayment($payment);

        $this->dispatchSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // TODO same shit here ... T_T

        // Generate number and key
        $changed = $this->generateNumber($payment);

        // TODO Timestampable behavior/listener
        $payment->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, ['amount', 'state'])) {
            $this->dispatchSaleContentChangeEvent($payment->getSale());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        $this->dispatchSaleContentChangeEvent($payment->getSale());
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
        $payment = $this->getPaymentFromEvent($event);

        if (!in_array($payment->getState(), PaymentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }
    }

    /**
     * Generates the sale number.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the sale number has been generated or not.
     */
    protected function generateNumber(PaymentInterface $payment)
    {
        if (0 == strlen($payment->getNumber())) {
            $this->numberGenerator->generate($payment);

            return true;
        }

        return false;
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function dispatchSaleContentChangeEvent(SaleInterface $sale);

    /**
     * Returns the payment from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return PaymentInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getPaymentFromEvent(ResourceEventInterface $event);
}
