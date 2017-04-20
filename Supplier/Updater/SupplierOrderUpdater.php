<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Updater;

use DateTime;
use Decimal\Decimal;
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
    protected GeneratorInterface $numberGenerator;
    protected StateResolverInterface $stateResolver;
    protected SupplierOrderCalculatorInterface $calculator;
    protected CurrencyConverterInterface $currencyConverter;


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

    public function updateCurrency(SupplierOrderInterface $order): bool
    {
        if (null === $supplier = $order->getSupplier()) {
            return false;
        }

        if ($order->getCurrency() !== $supplier->getCurrency()) {
            $order->setCurrency($supplier->getCurrency());

            return true;
        }

        return false;
    }

    public function updateCarrier(SupplierOrderInterface $order): bool
    {
        if (null === $supplier = $order->getSupplier()) {
            return false;
        }

        if (null === $order->getCarrier()) {
            $order->setCarrier($supplier->getCarrier());

            return true;
        }

        return false;
    }

    public function updateNumber(SupplierOrderInterface $order): bool
    {
        if (!empty($order->getNumber())) {
            return false;
        }

        $order->setNumber($this->numberGenerator->generate($order));

        return true;
    }

    public function updateState(SupplierOrderInterface $order): bool
    {
        $changed = $this->stateResolver->resolve($order);

        // If state is canceled, clear dates
        if ($order->getState() === SupplierOrderStates::STATE_CANCELED) {
            $order
                ->setEstimatedDateOfArrival(null)
                ->setPaymentDate(null)
                ->setForwarderDate(null)
                ->setCompletedAt(null);
        }
        // If order state is 'completed' and 'competed at' date is not set
        elseif (
            ($order->getState() === SupplierOrderStates::STATE_COMPLETED)
            && is_null($order->getCompletedAt())
        ) {
            // Set the 'completed at' date
            $order->setCompletedAt(new DateTime());
            $changed = true;
        }

        return $changed;
    }

    public function updateTotals(SupplierOrderInterface $order): bool
    {
        $changed = false;

        $tax = $this->calculator->calculatePaymentTax($order);
        if (!$order->getTaxTotal()->equals($tax)) {
            $order->setTaxTotal($tax);
            $changed = true;
        }

        $payment = $this->calculator->calculatePaymentTotal($order);
        if (!$order->getPaymentTotal()->equals($payment)) {
            $order->setPaymentTotal($payment);
            $changed = true;
        }

        if (null !== $order->getCarrier()) {
            $forwarder = $this->calculator->calculateForwarderTotal($order);
            if (!$order->getForwarderTotal()->equals($forwarder)) {
                $order->setForwarderTotal($forwarder);
                $changed = true;
            }
        } else {
            if (0 != $order->getForwarderFee()) {
                $order->setForwarderFee(new Decimal(0));
                $changed = true;
            }
            if (0 != $order->getCustomsTax()) {
                $order->setCustomsTax(new Decimal(0));
                $changed = true;
            }
            if (0 != $order->getCustomsVat()) {
                $order->setCustomsVat(new Decimal(0));
                $changed = true;
            }
            if (0 != $order->getForwarderTotal()) {
                $order->setForwarderTotal(new Decimal(0));
                $changed = true;
            }
            if (null !== $order->getForwarderDate()) {
                $order->setForwarderDate(null);
                $changed = true;
            }
            if (null !== $order->getForwarderDueDate()) {
                $order->setForwarderDueDate(null);
                $changed = true;
            }
        }

        return $changed;
    }

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
