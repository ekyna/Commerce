<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AbstractPaymentListener
 * @package Ekyna\Component\Commerce\Payment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentListener
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function __construct(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        $changed = 0 < count(array_intersect(['amount', 'state'], array_keys($event->getChangeSet())));

        // Generate number and key
        $changed = $this->generateNumber($payment) || $changed;

        // TODO Timestampable behavior/listener
        $payment
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $event->persistAndRecompute($payment);
            // Recompute the whole sale
            $event->persistAndRecompute($payment->getSale());
        }
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // TODO same shit here ... T_T

        $changed = array_key_exists('state', $event->getChangeSet());

        // Generate number and key
        $changed = $this->generateNumber($payment) || $changed;

        // TODO Timestampable behavior/listener
        $payment->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $event->persistAndRecompute($payment);
            // Recompute the whole sale
            $event->persistAndRecompute($payment->getSale());
            // TODO this does not trigger a sale update T_T
        }
    }

    /**
     * Delete event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onDelete(PersistenceEvent $event)
    {
        $payment = $this->getPaymentFromEvent($event);

        // Recompute the whole sale
        $event->persistAndRecompute($payment->getSale());
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
     * Returns the payment from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return PaymentInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getPaymentFromEvent(ResourceEventInterface $event);
}
