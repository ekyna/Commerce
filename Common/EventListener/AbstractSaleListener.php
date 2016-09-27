<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\PersistenceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AbstractSaleListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleListener
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var KeyGeneratorInterface
     */
    protected $keyGenerator;

    /**
     * @var AmountsCalculatorInterface
     */
    protected $amountCalculator;

    /**
     * @var WeightCalculatorInterface
     */
    protected $weightCalculator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface   $numberGenerator
     * @param KeyGeneratorInterface      $keyGenerator
     * @param AmountsCalculatorInterface $amountCalculator
     * @param WeightCalculatorInterface  $weightCalculator
     * @param StateResolverInterface     $stateResolver
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        KeyGeneratorInterface $keyGenerator,
        AmountsCalculatorInterface $amountCalculator,
        WeightCalculatorInterface $weightCalculator,
        StateResolverInterface $stateResolver
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->keyGenerator = $keyGenerator;
        $this->amountCalculator = $amountCalculator;
        $this->weightCalculator = $weightCalculator;
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onInsert(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        /*
         * TODO this is ugly :s
         * It should be a loop of operations/behaviors ...
         */

        $changed = false;

        // Generate number and key
        $changed = $this->generateNumber($sale) || $changed;
        $changed = $this->generateKey($sale) || $changed;

        // Handle identity
        $changed = $this->handleIdentity($sale) || $changed;

        // Handle addresses
        $changed = $this->handleAddresses($sale) || $changed;

        // Update totals
        $changed = $this->updateTotals($sale) || $changed;

        // Update state
        $changed = $this->updateState($sale) || $changed;

        // TODO Timestampable behavior/listener
        $sale
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($sale);
        }
    }

    /**
     * Update event handler.
     *
     * @param PersistenceEvent $event
     */
    public function onUpdate(PersistenceEvent $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // TODO same shit here ... T_T

        $changed = false;

        // Generate number and key
        $changed = $this->generateNumber($sale) || $changed;
        $changed = $this->generateKey($sale) || $changed;

        // Handle identity
        $changed = $this->handleIdentity($sale) || $changed;

        // Handle addresses
        if ($event->isChanged(['deliveryAddress', 'sameAddress'])) {
            $changed = $this->handleAddresses($sale) || $changed;
        }

        // TODO resolve/fix taxation adjustments if delivery address changed.
        // - Replace based on subject.
        // - If no subject, remove unexpected taxes ?

        // Update totals
        // TODO test that, maybe we have to use UnitOfWork::isCollectionScheduledFor*
        // TODO what about item's children ?
        if ($event->isChanged(['items', 'adjustments', 'payments'])) {
            $changed = $this->updateTotals($sale) || $changed;
        }

        // Update state
        $changed = $this->updateState($sale) || $changed;

        // TODO Timestampable behavior/listener
        $sale->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($sale);
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
        $sale = $this->getSaleFromEvent($event);

        // Stop if sale has valid payments
        if (null !== $payments = $sale->getPayments()) {
            foreach ($payments as $payment) {
                if (!in_array($payment->getState(), PaymentStates::getDeletableStates())) {
                    throw new IllegalOperationException();
                }
            }
        }
    }

    /**
     * Generates the number.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale number has been generated or not.
     */
    protected function generateNumber(SaleInterface $sale)
    {
        if (0 == strlen($sale->getNumber())) {
            $this->numberGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Generates the key.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale key has been generated or not.
     */
    protected function generateKey(SaleInterface $sale)
    {
        if (0 == strlen($sale->getKey())) {
            $this->keyGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Handles the identity.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function handleIdentity(SaleInterface $sale)
    {
        $changed = false;

        if (null !== $customer = $sale->getCustomer()) {
            if (0 == strlen($sale->getEmail())) {
                $sale->setEmail($customer->getEmail());
                $changed = true;
            }
            if (0 == strlen($sale->getCompany())) {
                $sale->setCompany($customer->getCompany());
                $changed = true;
            }
            if (0 == strlen($sale->getGender())) {
                $sale->setGender($customer->getGender());
                $changed = true;
            }
            if (0 == strlen($sale->getFirstName())) {
                $sale->setFirstName($customer->getFirstName());
                $changed = true;
            }
            if (0 == strlen($sale->getLastName())) {
                $sale->setLastName($customer->getLastName());
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * Handles the addresses.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function handleAddresses(SaleInterface $sale)
    {
        if ((null !== $deliveryAddress = $sale->getDeliveryAddress()) && $sale->getSameAddress()) {
            // Unset delivery address
            $sale->setDeliveryAddress(null);

            // Delete the delivery address
            // TODO $this->manager->delete($deliveryAddress);

            return true;
        }

        return false;
    }

    /**
     * Updates the totals.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateTotals(SaleInterface $sale)
    {
        // Amounts Totals
        $amounts = $this->amountCalculator->calculateSale($sale);

        $sale
            ->setNetTotal($amounts->getBase())
            ->setGrandTotal($amounts->getTotal());

        // Weight total
        $weightTotal = $this->weightCalculator->calculateSale($sale);

        $sale->setWeightTotal($weightTotal);

        return false;
    }

    /**
     * Updates the state.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateState(SaleInterface $sale)
    {
        return $this->stateResolver->resolve($sale);
    }

    /**
     * Fills the address identity fields if needed.
     *
     * @param AddressInterface $address
     * @param UserInterface    $user
     */
    /*private function handleAddressIdentity(AddressInterface $address, UserInterface $user)
    {
        if (null === $address->getGender()) {
            $address->setGender($user->getGender());
        }
        if (null === $address->getFirstName()) {
            $address->setFirstName($user->getFirstName());
        }
        if (null === $address->getLastName()) {
            $address->setLastName($user->getLastName());
        }
    }*/

    /**
     * Returns the sale from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SaleInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getSaleFromEvent(ResourceEventInterface $event);
}
