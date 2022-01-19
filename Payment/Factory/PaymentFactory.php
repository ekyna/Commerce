<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Factory;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;

/**
 * Class PaymentFactory
 * @package Ekyna\Component\Commerce\Payment\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentFactory implements PaymentFactoryInterface
{
    protected FactoryHelperInterface      $factoryHelper;
    protected CurrencyRepositoryInterface $currencyRepository;
    protected PaymentUpdaterInterface     $paymentUpdater;
    protected PaymentCalculatorInterface  $paymentCalculator;
    protected CurrencyConverterInterface  $currencyConverter;

    public function __construct(
        FactoryHelperInterface      $factoryHelper,
        PaymentUpdaterInterface     $updater,
        PaymentCalculatorInterface  $calculator,
        CurrencyConverterInterface  $converter,
        CurrencyRepositoryInterface $repository
    ) {
        $this->factoryHelper = $factoryHelper;
        $this->paymentUpdater = $updater;
        $this->paymentCalculator = $calculator;
        $this->currencyConverter = $converter;
        $this->currencyRepository = $repository;
    }

    public function createPayment(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        $payment = $this->create($subject, $method)->setRefund(false);

        $amount = $this
            ->paymentCalculator
            ->calculateExpectedPaymentAmount($subject, $payment->getCurrency()->getCode());

        $payment->setAmount($amount);

        $this->paymentUpdater->fixRealAmount($payment);

        return $payment;
    }

    public function createRefund(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        $payment = $this->create($subject, $method)->setRefund(true);

        $amount = $this->paymentCalculator->calculateExpectedRefundAmount($subject, $payment->getCurrency()->getCode());

        $payment->setAmount($amount);

        $this->paymentUpdater->fixRealAmount($payment);

        return $payment;
    }

    protected function create(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        if (!$subject instanceof SaleInterface) {
            throw new UnexpectedTypeException($subject, SaleInterface::class);
        }

        $payment = $this->factoryHelper->createPaymentForSale($subject);

        if ($method->isDefaultCurrency()) {
            $currency = $this->currencyRepository->findDefault();
        } else {
            $currency = $subject->getCurrency();
        }

        $payment->setCurrency($currency);

        if ($method->isCredit() || $method->isOutstanding()) {
            $date = $subject->getExchangeDate() ?? new \DateTime();
        } else {
            $date = new \DateTime();
        }

        $payment->setExchangeDate($date);

        $rate = $this
            ->currencyConverter
            ->getSubjectExchangeRate($payment, $this->currencyConverter->getDefaultCurrency(), $currency->getCode());

        $payment->setMethod($method)->setExchangeRate($rate);

        return $payment;
    }
}
