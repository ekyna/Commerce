<?php

namespace Ekyna\Component\Commerce\Payment\EventListener;

use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
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
     * @var KeyGeneratorInterface
     */
    protected $keyGenerator;


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
     * Sets the key generator.
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function setKeyGenerator(KeyGeneratorInterface $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
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
        $changed = $this->generateKey($payment) || $changed;

        // TODO Timestampable behavior/listener
        $payment
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        $sale = $payment->getSale();
        $sale->addPayment($payment);

        $this->scheduleSaleContentChangeEvent($sale);
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
        $changed = $this->generateKey($payment) || $changed;

        // TODO Timestampable behavior/listener
        $payment->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($payment);
        }

        if ($this->persistenceHelper->isChanged($payment, ['amount', 'state'])) {
            $this->scheduleSaleContentChangeEvent($payment->getSale());
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

        $this->scheduleSaleContentChangeEvent($payment->getSale());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        /*$payment = $this->getPaymentFromEvent($event);
        // TODO assert updateable states
        if (!in_array($payment->getState(), PaymentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }*/
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
     * Generates the payment number.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment has been changed or not.
     */
    protected function generateNumber(PaymentInterface $payment)
    {
        if (0 == strlen($payment->getNumber())) {
            if (null === $sale = $payment->getSale()) {
                return false;
            }

            $number = 1;
            foreach ($sale->getPayments() as $p) {
                if (preg_match('~\d+-(\d+)~', $p->getNumber(), $matches)) {
                    $n = intval($matches[1]);
                    if ($number <= $n) {
                        $number = $n + 1;
                    }
                }
            }

            $payment->setNumber(sprintf('%s-%s', $sale->getNumber(), $number));

            return true;
        }

        return false;
    }

    /**
     * Generates the payment key.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment has been changed or not.
     */
    protected function generateKey(PaymentInterface $payment)
    {
        if (0 == strlen($payment->getKey())) {
            $this->keyGenerator->generate($payment);

            return true;
        }

        return false;
    }

    /**
     * Schedules the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale);

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
