<?php

namespace Ekyna\Component\Commerce\Tests;

use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class TestCase extends BaseTestCase
{
    protected const DEFAULT_CURRENCY = 'EUR';

    protected const PAYMENT_METHOD_DEFAULT     = 0;
    protected const PAYMENT_METHOD_MANUAL      = 1;
    protected const PAYMENT_METHOD_OUTSTANDING = 2;
    protected const PAYMENT_METHOD_CREDIT      = 3;

    /**
     * @var CustomerGroupRepositoryInterface|MockObject
     */
    private $customerGroupRepositoryMock;

    /**
     * @var CurrencyRepositoryInterface|MockObject
     */
    private $currencyRepositoryMock;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var DiscountResolverInterface
     */
    private $discountResolver;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var PersistenceHelperInterface|MockObject
     */
    private $persistenceHelper;

    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $eventDispatcher;

    /**
     * @var SubjectHelperInterface|MockObject
     */
    private $subjectHelper;

    /**
     * @var array|PaymentMethodInterface[]
     */
    private $paymentMethods = [];


    protected function tearDown(): void
    {
        $this->customerGroupRepositoryMock = null;
        $this->currencyRepositoryMock = null;
        $this->currencyConverter = null;
        $this->taxResolver = null;
        $this->discountResolver = null;
        $this->saleFactory = null;
        $this->persistenceHelper = null;
        $this->eventDispatcher = null;
        $this->subjectHelper = null;
        $this->paymentMethods = [];
    }

    /**
     * Returns a customer group repository mock.
     *
     * @return CustomerGroupRepositoryInterface|MockObject
     */
    protected function getCustomerGroupRepositoryMock(): CustomerGroupRepositoryInterface
    {
        if (null !== $this->customerGroupRepositoryMock) {
            return $this->customerGroupRepositoryMock;
        }

        $mock = $this->createMock(CustomerGroupRepositoryInterface::class);

        $mock->method('findDefault')->willReturn(Fixtures::getDefaultCustomerGroup());

        return $this->customerGroupRepositoryMock = $mock;
    }

    /**
     * Returns a currency repository mock.
     *
     * @return CurrencyRepositoryInterface|MockObject
     */
    protected function getCurrencyRepositoryMock(): CurrencyRepositoryInterface
    {
        if (null !== $this->currencyRepositoryMock) {
            return $this->currencyRepositoryMock;
        }

        $mock = $this->createMock(CurrencyRepositoryInterface::class);

        $mock->method('findDefault')->willReturn(Fixtures::getDefaultCurrency());
        $mock->method('findOneByCode')->with('EUR')->willReturn(Fixtures::getCurrencyByCode('EUR'));
        $mock->method('findOneByCode')->with('USD')->willReturn(Fixtures::getCurrencyByCode('USD'));

        return $this->currencyRepositoryMock = $mock;
    }

    /**
     * Returns the currency converter.
     *
     * @return CurrencyConverterInterface
     */
    protected function getCurrencyConverter(): CurrencyConverterInterface
    {
        if (null !== $this->currencyConverter) {
            return $this->currencyConverter;
        }

        return $this->currencyConverter = new CurrencyConverter(new ArrayExchangeRateProvider([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.8,
        ]), self::DEFAULT_CURRENCY);
    }

    /**
     * Returns the tax resolver mock.
     *
     * @return TaxResolverInterface|MockObject
     */
    protected function getTaxResolverMock(): TaxResolverInterface
    {
        if (null !== $this->taxResolver) {
            return $this->taxResolver;
        }

        return $this->taxResolver = $this->createMock(TaxResolverInterface::class);
    }

    /**
     * Returns the discount resolver mock.
     *
     * @return DiscountResolverInterface|MockObject
     */
    protected function getDiscountResolverMock(): DiscountResolverInterface
    {
        if (null !== $this->discountResolver) {
            return $this->discountResolver;
        }

        return $this->discountResolver = $this->createMock(DiscountResolverInterface::class);
    }

    /**
     * Returns a sale factory.
     *
     * @return SaleFactoryInterface
     */
    protected function getSaleFactory(): SaleFactoryInterface
    {
        if (null !== $this->saleFactory) {
            return $this->saleFactory;
        }

        return $this->saleFactory = new SaleFactory();
    }

    /**
     * Returns the persistence helper mock.
     *
     * @return PersistenceHelperInterface|MockObject
     */
    protected function getPersistenceHelperMock(): PersistenceHelperInterface
    {
        if (null !== $this->persistenceHelper) {
            return $this->persistenceHelper;
        }

        return $this->persistenceHelper = $this->createMock(PersistenceHelperInterface::class);
    }

    /**
     * Returns the event dispatcher mock.
     *
     * @return EventDispatcherInterface|MockObject
     */
    protected function getEventDispatcherMock(): EventDispatcherInterface
    {
        if (null !== $this->eventDispatcher) {
            return $this->eventDispatcher;
        }

        return $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    /**
     * Returns the subject helper mock.
     *
     * @return SubjectHelperInterface|MockObject
     */
    protected function getSubjectHelperMock(): SubjectHelperInterface
    {
        if (null !== $this->subjectHelper) {
            return $this->subjectHelper;
        }

        return $this->subjectHelper = $this->createMock(SubjectHelperInterface::class);
    }

    /**
     * Returns a payment method mock.
     *
     * @param int $type
     *
     * @return PaymentMethodInterface
     */
    protected function getPaymentMethodMock(int $type = 0): PaymentMethodInterface
    {
        if (isset($this->paymentMethods[$type])) {
            return $this->paymentMethods[$type];
        }

        /** @var PaymentMethodInterface|MockObject $method */
        $method = $this->createMock(PaymentMethodInterface::class);

        $method->method('isManual')->willReturn($type === self::PAYMENT_METHOD_MANUAL);
        $method->method('isOutstanding')->willReturn($type === self::PAYMENT_METHOD_OUTSTANDING);
        $method->method('isCredit')->willReturn($type === self::PAYMENT_METHOD_CREDIT);

        return $this->paymentMethods[$type] = $method;
    }
}
