<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Calculator\CalculatorInterface;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\KeySubjectInterface;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\PersistenceEvent;

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
     * @var CalculatorInterface
     */
    protected $calculator;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     * @param KeyGeneratorInterface    $keyGenerator
     * @param CalculatorInterface      $calculator
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        KeyGeneratorInterface $keyGenerator,
        CalculatorInterface $calculator
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->keyGenerator = $keyGenerator;
        $this->calculator = $calculator;
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
        //$changed = $this->generateNumber($order) || $changed;
        //$changed = $this->generateKey($order) || $changed;

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
        if ($event->isChanged(['items', 'adjustments', 'payments'])) {
            $changed = $this->updateTotals($sale) || $changed;
        }

        // TODO Timestampable behavior/listener
        $sale->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $event->persistAndRecompute($sale);
        }
    }

    /**
     * Generates the sale number.
     *
     * @param NumberSubjectInterface $sale
     *
     * @return bool Whether the sale number has been generated or not.
     */
    protected function generateNumber(NumberSubjectInterface $sale)
    {
        if (0 == strlen($sale->getNumber())) {
            $this->numberGenerator->generate($sale);

            return true;
        }

        return false;
    }

    /**
     * Generates the sale key.
     *
     * @param KeySubjectInterface $sale
     *
     * @return bool Whether the sale key has been generated or not.
     */
    protected function generateKey(KeySubjectInterface $sale)
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
     * @return bool Whether the order has been changed or not.
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
     * Updates the sale totals.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the order has been changed or not.
     */
    protected function updateTotals(SaleInterface $sale)
    {
        $amounts = $this->calculator->calculateSale($sale);

        $sale
            ->setNetTotal($amounts->getBase())
            ->setGrandTotal($amounts->getTotal());
            // TODO Total weight

        return false;
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
     * @param PersistenceEvent $event
     *
     * @return SaleInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getSaleFromEvent(PersistenceEvent $event);
}
