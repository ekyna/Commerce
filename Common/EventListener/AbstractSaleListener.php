<?php

namespace Ekyna\Component\Commerce\Common\EventListener;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountsCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractSaleListener
 * @package Ekyna\Component\Commerce\Common\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var KeyGeneratorInterface
     */
    protected $keyGenerator;

    /**
     * @var AdjustmentBuilderInterface
     */
    protected $adjustmentBuilder;

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
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
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
     * Sets the adjustment builder.
     *
     * @param AdjustmentBuilderInterface $adjustmentBuilder
     */
    public function setAdjustmentBuilder(AdjustmentBuilderInterface $adjustmentBuilder)
    {
        $this->adjustmentBuilder = $adjustmentBuilder;
    }

    /**
     * Sets the amounts calculator.
     *
     * @param AmountsCalculatorInterface $amountCalculator
     */
    public function setAmountsCalculator(AmountsCalculatorInterface $amountCalculator)
    {
        $this->amountCalculator = $amountCalculator;
    }

    /**
     * Sets the weight calculator.
     *
     * @param WeightCalculatorInterface $weightCalculator
     */
    public function setWeightCalculator(WeightCalculatorInterface $weightCalculator)
    {
        $this->weightCalculator = $weightCalculator;
    }

    /**
     * Sets the state resolver.
     *
     * @param StateResolverInterface $stateResolver
     */
    public function setStateResolver(StateResolverInterface $stateResolver)
    {
        $this->stateResolver = $stateResolver;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
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

        // Update taxation
        $changed = $this->updateTaxation($sale) || $changed;

        // Update totals
        $changed = $this->updateTotals($sale) || $changed;

        // Update state
        $changed = $this->updateState($sale) || $changed;

        // TODO Timestampable behavior/listener
        $sale
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) { // TODO
            $this->persistenceHelper->persistAndRecompute($sale);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // TODO same shit here ... T_T

        $changed = $doTotalsUpdate = false;

        // Generate number and key
        $changed = $this->generateNumber($sale) || $changed;
        $changed = $this->generateKey($sale) || $changed;

        // Handle identity
        $changed = $this->handleIdentity($sale) || $changed;

        // Handle addresses
        if ($this->persistenceHelper->isChanged($sale, ['deliveryAddress', 'sameAddress'])) {
            $changed = $this->handleAddresses($sale) || $changed;
        }

        // TODO Timestampable behavior/listener
        $sale->setUpdatedAt(new \DateTime());
        $changed = true;

        // Recompute to get an update-to-date change set.
        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
        }

        $this->onTaxResolution($event);
    }

    /**
     * Tax resolution event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onTaxResolution(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        $changed = $doTotalsUpdate = false;

        // Update taxation
        if ($event->getHard() || $this->isTaxationUpdateNeeded($sale)) {
            if ($this->updateTaxation($sale)) {
                $changed = $doTotalsUpdate = true;
            }
        } elseif ($this->isShipmentTaxationUpdateNeeded($sale)) {
            if ($this->updateShipmentTaxation($sale)) {
                $changed = $doTotalsUpdate = true;
            }
        }

        // Update totals
        // TODO create and use isTotalsUpdateNeeded() method
        if ($event->getHard() || $doTotalsUpdate) {
            $changed = $this->updateTotals($sale) || $changed;
        }

        // Update state
        // TODO create and use isStateUpdateNeeded() method
        $changed = $this->updateState($sale) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
        }
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $sale = $this->getSaleFromEvent($event);

        // Update totals
        // TODO create and use isTotalsUpdateNeeded() method
        $changed = $this->updateTotals($sale);

        // Update state
        // TODO create and use isStateUpdateNeeded() method
        $changed = $this->updateState($sale) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale);
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
        if ($event->getHard()) {
            return;
        }

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
     * Returns whether or not the taxation adjustments should be updated.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isTaxationUpdateNeeded(SaleInterface $sale)
    {
        // TODO Abort if completed (order) ?

        // TODO Get tax resolution mode. (by invoice/delivery/origin).

        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        // Watch for tax exempt, customer group or customer change
        if (isset($saleCs['taxExempt']) || isset($saleCs['customerGroup']) || isset($saleCs['customer'])) {
            return true;
        }

        // Watch for delivery country change
        $oldCountry = $newCountry = null;

        // Resolve the old tax resolution target country
        $oldSameAddress = isset($saleCs['sameAddress']) ? $saleCs['sameAddress'][0] : $sale->isSameAddress();
        if ($oldSameAddress) {
            $oldAddress = isset($saleCs['invoiceAddress']) ? $saleCs['invoiceAddress'][0] : $sale->getInvoiceAddress();
        } else {
            $oldAddress = isset($saleCs['deliveryAddress']) ? $saleCs['deliveryAddress'][0] : $sale->getDeliveryAddress();
        }
        if (null !== $oldAddress) {
            $oldAddressCs = $this->persistenceHelper->getChangeSet($oldAddress);
            $oldCountry = isset($oldAddressCs['country']) ? $oldAddressCs['country'][0] : $oldAddress->getCountry();
        }

        // Resolve the new tax resolution target country
        $newAddress = $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
        if (null !== $newAddress) {
            $newCountry = $newAddress->getCountry();
        }

        if ($oldCountry != $newCountry) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether or not the shipment related taxation adjustments should be updated.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isShipmentTaxationUpdateNeeded(SaleInterface $sale)
    {
        $saleCs = $this->persistenceHelper->getChangeSet($sale);

        return isset($saleCs['preferredShipmentMethod']);
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
        if ((null !== $deliveryAddress = $sale->getDeliveryAddress()) && $sale->isSameAddress()) {
            // Unset delivery address
            $sale->setDeliveryAddress(null);

            // Delete the delivery address
            $this->persistenceHelper->remove($deliveryAddress);

            return true;
        }

        return false;
    }

    /**
     * Updates the whole sale taxation adjustments.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateTaxation(SaleInterface $sale)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSaleItems($sale, true)
            || $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, true);
    }

    /**
     * Updates the sale shipment related taxation adjustments.
     *
     * @param SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    protected function updateShipmentTaxation(SaleInterface $sale)
    {
        return $this->adjustmentBuilder->buildTaxationAdjustmentsForSale($sale, true);
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
        $change = false;

        // Amounts Totals
        $amounts = $this->amountCalculator->calculateSale($sale);
        if ($sale->getNetTotal() != $amounts->getBase()) {
            $sale->setNetTotal($amounts->getBase());
            $change = true;
        }
        if ($sale->getGrandTotal() != $amounts->getTotal()) {
            $sale->setGrandTotal($amounts->getTotal());
            $change = true;
        }

        // Weight total
        $weightTotal = $this->weightCalculator->calculateSale($sale);
        if ($sale->getWeightTotal() != $weightTotal) {
            $sale->setWeightTotal($weightTotal);
            $change = true;
        }

        return $change;
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
     * Returns the sale from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return SaleInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getSaleFromEvent(ResourceEventInterface $event);
}
