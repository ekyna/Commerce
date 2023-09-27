<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests;

use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface as CommonWeightCalculator;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelper;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculatorInterface as ShipmentWeightCalculator;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentAddressResolverInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProviderInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Ekyna\Component\Resource\Tests\PhpUnit\TestCase as BaseTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class TestCase extends BaseTestCase
{
    protected const DEFAULT_CURRENCY = 'EUR';

    // TODO Remove: use Fixtures constants
    protected const PAYMENT_METHOD_DEFAULT     = 0;
    protected const PAYMENT_METHOD_MANUAL      = 1;
    protected const PAYMENT_METHOD_OUTSTANDING = 2;
    protected const PAYMENT_METHOD_CREDIT      = 3;

    private ?CurrencyConverterInterface $currencyConverter = null;
    private ?FactoryHelperInterface     $saleFactory       = null;

    /**
     * @var array<int, PaymentMethodInterface>
     */
    private array $paymentMethods = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->currencyConverter = null;
        $this->saleFactory = null;
        $this->paymentMethods = [];
        Fixture::clear();
    }

    /**
     * Returns a sale factory helper.
     *
     * @return FactoryHelperInterface
     */
    protected function getFactoryHelper(): FactoryHelperInterface
    {
        if (null !== $this->saleFactory) {
            return $this->saleFactory;
        }

        return $this->saleFactory = new FactoryHelper(
            $this->getFactoryFactoryMock()
        );
    }

    /**
     * Returns the amount calculator mock.
     */
    protected function getAmountCalculatorMock(): AmountCalculatorInterface|MockObject
    {
        return $this->mockService(AmountCalculatorInterface::class);
    }

    /**
     * Returns the (common) weight calculator mock.
     */
    protected function getCommonWeightCalculatorMock(): CommonWeightCalculator|MockObject
    {
        return $this->mockService(CommonWeightCalculator::class);
    }

    /**
     * Returns the context provider mock.
     */
    protected function getContextProviderMock(): ContextProviderInterface|MockObject
    {
        return $this->mockService(ContextProviderInterface::class);
    }

    /**
     * Returns the country provider mock.
     */
    protected function getCountryProviderMock(): CountryProviderInterface|MockObject
    {
        if ($this->hasMock(CountryProviderInterface::class)) {
            return $this->getMock(CountryProviderInterface::class);
        }

        $mock = $this->mockService(CountryProviderInterface::class);

        $fr = Fixture::country(Fixture::COUNTRY_FR);

        $mock->method('getDefault')->willReturn($fr);
        $mock->method('getCountry')->willReturn($fr);

        return $mock;
    }

    /**
     * Returns a currency repository mock.
     */
    protected function getCountryRepositoryMock(): CountryRepositoryInterface|MockObject
    {
        if ($this->hasMock(CountryRepositoryInterface::class)) {
            return $this->getMock(CountryRepositoryInterface::class);
        }

        $mock = $this->mockService(CountryRepositoryInterface::class);

        $mock
            ->method('findDefault')
            ->willReturn(Fixture::country());

        $mock
            ->method('getDefaultCode')
            ->willReturn(Fixture::COUNTRY_FR);

        $mock
            ->method('findOneByCode')
            ->willReturnMap([
                [Fixture::COUNTRY_FR, Fixture::country(Fixture::COUNTRY_FR)],
                [Fixture::COUNTRY_US, Fixture::country(Fixture::COUNTRY_US)],
            ]);

//        $mock
//            ->method('findOneByCode')
//            ->with(Fixture::COUNTRY_FR)
//            ->willReturn(Fixture::country(Fixture::COUNTRY_FR));
//
//        $mock
//            ->method('findOneByCode')
//            ->with(Fixture::COUNTRY_US)
//            ->willReturn(Fixture::country(Fixture::COUNTRY_US));

        return $mock;
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
            'EUR/USD' => new Decimal('1.25'),
            'USD/EUR' => new Decimal('0.8'),
            'EUR/GBP' => new Decimal('0.9'),
            'GBP/EUR' => new Decimal('1.111111'),
            'USD/GBP' => new Decimal('0.72'),
            'GBP/USD' => new Decimal('1.388889'),
        ]), self::DEFAULT_CURRENCY);
    }

    /**
     * Returns a currency repository mock.
     */
    protected function getCurrencyRepositoryMock(): CurrencyRepositoryInterface|MockObject
    {
        if ($this->hasMock(CurrencyRepositoryInterface::class)) {
            return $this->getMock(CurrencyRepositoryInterface::class);
        }

        $mock = $this->mockService(CurrencyRepositoryInterface::class);

        $mock
            ->method('findDefault')
            ->willReturn(Fixture::currency());

        $mock
            ->method('findOneByCode')
            ->willReturnMap([
                [Fixture::CURRENCY_EUR, Fixture::currency(Fixture::CURRENCY_EUR)],
                [Fixture::CURRENCY_USD, Fixture::currency(Fixture::CURRENCY_USD)],
            ]);

//        $mock
//            ->method('findOneByCode')
//            ->with(Fixture::CURRENCY_EUR)
//            ->willReturn(Fixture::currency(Fixture::CURRENCY_EUR));
//
//        $mock
//            ->method('findOneByCode')
//            ->with(Fixture::CURRENCY_USD)
//            ->willReturn(Fixture::currency(Fixture::CURRENCY_USD));

        return $mock;
    }

    /**
     * Returns a customer group repository mock.
     */
    protected function getCustomerGroupRepositoryMock(): CustomerGroupRepositoryInterface|MockObject
    {
        if ($this->hasMock(CustomerGroupRepositoryInterface::class)) {
            return $this->getMock(CustomerGroupRepositoryInterface::class);
        }

        $mock = $this->mockService(CustomerGroupRepositoryInterface::class);

        $mock->method('findDefault')->willReturn(Fixture::customerGroup());

        return $mock;
    }

    /**
     * Returns the discount resolver mock.
     */
    protected function getDiscountResolverMock(): DiscountResolverInterface|MockObject
    {
        return $this->mockService(DiscountResolverInterface::class);
    }

    /**
     * Returns the event dispatcher mock.
     */
    protected function getEventDispatcherMock(): EventDispatcherInterface|MockObject
    {
        return $this->mockService(EventDispatcherInterface::class);
    }

    /**
     * Returns the entity manager mock.
     *
     * @TODO break dependency
     */
    protected function getEntityManagerMock(): EntityManagerInterface|MockObject
    {
        return $this->mockService(EntityManagerInterface::class);
    }

    /**
     * Returns a payment method mock.
     */
    protected function getPaymentMethodMock(int $type = 0): PaymentMethodInterface|MockObject
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

    /**
     * Returns the purchase cost guesser mock.
     */
    protected function getPurchaseCostGuesserMock(): SubjectCostGuesserInterface|MockObject
    {
        return $this->mockService(SubjectCostGuesserInterface::class);
    }

    /**
     * Returns the sale factory helper mock.
     */
    protected function getFactoryHelperMock(): FactoryHelperInterface|MockObject
    {
        return $this->mockService(FactoryHelperInterface::class);
    }

    /**
     * Returns the shipment address resolver mock.
     */
    protected function getShipmentAddressResolverMock(): ShipmentAddressResolverInterface|MockObject
    {
        return $this->mockService(ShipmentAddressResolverInterface::class);
    }

    /**
     * Returns the (shipment) weight calculator mock.
     */
    protected function getShipmentWeightCalculatorMock(): ShipmentWeightCalculator|MockObject
    {
        return $this->mockService(ShipmentWeightCalculator::class);
    }

    /**
     * Returns the shipment price resolver mock.
     */
    protected function getShipmentPriceResolverMock(): ShipmentPriceResolverInterface|MockObject
    {
        return $this->mockService(ShipmentPriceResolverInterface::class);
    }

    /**
     * Returns the stock unit repository mock.
     */
    protected function getStockUnitRepositoryMock(): StockUnitRepositoryInterface|MockObject
    {
        return $this->mockService(StockUnitRepositoryInterface::class);
    }

    /**
     * Returns the subject helper mock.
     */
    protected function getSubjectHelperMock(): SubjectHelperInterface|MockObject
    {
        return $this->mockService(SubjectHelperInterface::class);
    }

    /**
     * Returns the supplier order item repository mock.
     */
    protected function getSupplierOrderItemRepositoryMock(): SupplierOrderItemRepositoryInterface|MockObject
    {
        return $this->mockService(SupplierOrderItemRepositoryInterface::class);
    }

    /**
     * Returns the supplier product repository mock.
     */
    protected function getSupplierProductRepositoryMock(): SupplierProductRepositoryInterface|MockObject
    {
        return $this->mockService(SupplierProductRepositoryInterface::class);
    }

    /**
     * Returns the tax resolver mock.
     */
    protected function getTaxResolverMock(): TaxResolverInterface|MockObject
    {
        return $this->mockService(TaxResolverInterface::class);
    }

    /**
     * Returns a tax rule repository mock.
     */
    protected function getTaxRuleRepositoryMock(): TaxRuleRepositoryInterface|MockObject
    {
        return $this->mockService(TaxRuleRepositoryInterface::class);
    }

    /**
     * Returns the warehouse provider mock.
     */
    protected function getWarehouseProviderMock(): WarehouseProviderInterface|MockObject
    {
        return $this->mockService(WarehouseProviderInterface::class);
    }

    /**
     * Asserts that the given object will be persisted or removed using the persistence helper.
     *
     * Options : [
     *     'remove'  => true,  // Check remove instead of persistAndRecompute
     *     'order'    => null, // Invocation rule (once by default)
     *     'schedule' => null, // With event schedule (bool)
     * ]
     *
     * @param object $object
     * @param array  $options
     */
    protected function assertPersistence(object $object, array $options = []): void
    {
        $options = array_replace([
            'remove'   => false,
            'order'    => null,
            'schedule' => null,
        ], $options);

        $arguments = [$object];

        if (!is_null($options['schedule'])) {
            $arguments[] = $options['schedule'];
        }

        $this
            ->getPersistenceHelperMock()
            ->expects($options['order'] ?? self::once())
            ->method($options['remove'] ? 'remove' : 'persistAndRecompute')
            ->with(...$arguments);
    }
}
