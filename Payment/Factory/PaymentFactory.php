<?php

namespace Ekyna\Component\Commerce\Payment\Factory;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
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
    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

    /**
     * @var PaymentUpdaterInterface
     */
    protected $paymentUpdater;

    /**
     * @var PaymentCalculatorInterface
     */
    protected $paymentCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface        $factory
     * @param PaymentUpdaterInterface     $updater
     * @param PaymentCalculatorInterface  $calculator
     * @param CurrencyConverterInterface  $converter
     * @param CurrencyRepositoryInterface $repository
     */
    public function __construct(
        SaleFactoryInterface $factory,
        PaymentUpdaterInterface $updater,
        PaymentCalculatorInterface $calculator,
        CurrencyConverterInterface $converter,
        CurrencyRepositoryInterface $repository
    ) {
        $this->saleFactory = $factory;
        $this->paymentUpdater = $updater;
        $this->paymentCalculator = $calculator;
        $this->currencyConverter = $converter;
        $this->currencyRepository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function createPayment(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        $payment = $this->create($subject, $method)->setRefund(false);

        $amount = $this->paymentCalculator->calculateExpectedPaymentAmount($subject, $payment->getCurrency()->getCode());

        $payment->setAmount($amount);

        $this->paymentUpdater->fixRealAmount($payment);

        return $payment;
    }

    /**
     * @inheritDoc
     */
    public function createRefund(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        $payment = $this->create($subject, $method)->setRefund(true);

        $amount = $this->paymentCalculator->calculateExpectedRefundAmount($subject, $payment->getCurrency()->getCode());

        $payment->setAmount($amount);

        $this->paymentUpdater->fixRealAmount($payment);

        return $payment;
    }

    /**
     * Creates a payment.
     *
     * @param PaymentSubjectInterface $subject
     * @param PaymentMethodInterface  $method
     *
     * @return PaymentInterface
     */
    protected function create(PaymentSubjectInterface $subject, PaymentMethodInterface $method): PaymentInterface
    {
        if ($subject instanceof SaleInterface) {
            $payment = $this->saleFactory->createPaymentForSale($subject);
        } else {
            throw new UnexpectedValueException("Expected instance of " . SaleInterface::class);
        }

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

        return $payment->setMethod($method)->setExchangeRate($rate);
    }
}
