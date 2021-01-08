<?php

namespace Ekyna\Component\Commerce\Supplier\Updater;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

/**
 * Class SupplierOrderUpdater
 * @package Ekyna\Component\Commerce\Supplier\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderUpdater implements SupplierOrderUpdaterInterface
{
    /**
     * @var GeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var StateResolverInterface
     */
    protected $stateResolver;

    /**
     * @var SupplierOrderCalculatorInterface
     */
    protected $calculator;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


    /**
     * Constructor.
     *
     * @param GeneratorInterface               $numberGenerator
     * @param StateResolverInterface           $stateResolver
     * @param SupplierOrderCalculatorInterface $calculator
     * @param CurrencyConverterInterface       $currencyConverter
     */
    public function __construct(
        GeneratorInterface $numberGenerator,
        StateResolverInterface $stateResolver,
        SupplierOrderCalculatorInterface $calculator,
        CurrencyConverterInterface $currencyConverter)
    {
        $this->numberGenerator = $numberGenerator;
        $this->stateResolver = $stateResolver;
        $this->calculator = $calculator;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @inheritdoc
     */
    public function updateNumber(SupplierOrderInterface $order): bool
    {
        if (!empty($order->getNumber())) {
            return false;
        }

        $order->setNumber($this->numberGenerator->generate($order));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateState(SupplierOrderInterface $order): bool
    {
        $changed = $this->stateResolver->resolve($order);

        // If state is canceled, clear dates
        if ($order->getState() === SupplierOrderStates::STATE_CANCELED) {
            $order
                ->setEstimatedDateOfArrival()
                ->setPaymentDate()
                ->setForwarderDate()
                ->setCompletedAt();
        }
        // If order state is 'completed' and 'competed at' date is not set
        elseif (
            ($order->getState() === SupplierOrderStates::STATE_COMPLETED)
            && is_null($order->getCompletedAt())
        ) {
            // Set the 'completed at' date
            $order->setCompletedAt(new \DateTime());
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateTotals(SupplierOrderInterface $order): bool
    {
        $changed = false;

        $tax = $this->calculator->calculatePaymentTax($order);
        if ($tax != $order->getTaxTotal()) {
            $order->setTaxTotal($tax);
            $changed = true;
        }

        $payment = $this->calculator->calculatePaymentTotal($order);
        if ($payment != $order->getPaymentTotal()) {
            $order->setPaymentTotal($payment);
            $changed = true;
        }

        if (null !== $order->getCarrier()) {
            $forwarder = $this->calculator->calculateForwarderTotal($order);
            if ($forwarder != $order->getForwarderTotal()) {
                $order->setForwarderTotal($forwarder);
                $changed = true;
            }
        } else {
            if (0 != $order->getForwarderFee()) {
                $order->setForwarderFee(0);
                $changed = true;
            }
            if (0 != $order->getCustomsTax()) {
                $order->setCustomsTax(0);
                $changed = true;
            }
            if (0 != $order->getCustomsVat()) {
                $order->setCustomsVat(0);
                $changed = true;
            }
            if (0 != $order->getForwarderTotal()) {
                $order->setForwarderTotal(0);
                $changed = true;
            }
            if (null !== $order->getForwarderDate()) {
                $order->setForwarderDate();
                $changed = true;
            }
            if (null !== $order->getForwarderDueDate()) {
                $order->setForwarderDueDate();
                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateExchangeRate(SupplierOrderInterface $order): bool
    {
        // TODO Remove when supplier order payments will be implemented.
        if (null !== $order->getExchangeRate()) {
            return false;
        }

        if (SupplierOrderStates::isDeletableState($order->getState())) {
            return false;
        }

        if (null === $date = $order->getPaymentDate()) {
            return false;
        }

        $order->setExchangeDate($date);

        return $this->currencyConverter->setSubjectExchangeRate($order);
    }
}
