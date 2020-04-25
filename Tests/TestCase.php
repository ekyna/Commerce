<?php

namespace Ekyna\Component\Commerce\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculatorInterface as CommonWeightCalculator;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
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
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
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

    /**
     * @var array
     */
    private $mocks;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var array|PaymentMethodInterface[]
     */
    private $paymentMethods = [];


    protected function tearDown(): void
    {
        $this->mocks = [];
        $this->currencyConverter = null;
        $this->saleFactory = null;
        $this->paymentMethods = [];
        Fixture::clear();
    }

    /**
     * @param string $interface
     *
     * @return bool
     */
    private function hasMock(string $interface): bool
    {
        return isset($this->mocks[$interface]);
    }

    /**
     * @param string $interface
     *
     * @return MockObject
     */
    private function getMock(string $interface): MockObject
    {
        if (!$this->hasMock($interface)) {
            throw new RuntimeException("$interface has not been mocked yet.");
        }

        return $this->mocks[$interface];
    }

    /**
     * @inheritDoc
     */
    protected function mockService($interface): MockObject
    {
        if ($this->hasMock($interface)) {
            return $this->getMock($interface);
        }

        return $this->mocks[$interface] = parent::createMock($interface);
    }

    /**
     * Returns the amount calculator mock.
     *
     * @return AmountCalculatorInterface|MockObject
     */
    protected function getAmountCalculatorMock(): AmountCalculatorInterface
    {
        return $this->mockService(AmountCalculatorInterface::class);
    }

    /**
     * Returns the (common) weight calculator mock.
     *
     * @return CommonWeightCalculator|MockObject
     */
    protected function getCommonWeightCalculatorMock(): CommonWeightCalculator
    {
        return $this->mockService(CommonWeightCalculator::class);
    }

    /**
     * Returns the context provider mock.
     *
     * @return ContextProviderInterface|MockObject
     */
    protected function getContextProviderMock(): ContextProviderInterface
    {
        return $this->mockService(ContextProviderInterface::class);
    }

    /**
     * Returns the country provider mock.
     *
     * @return CountryProviderInterface|MockObject
     */
    protected function getCountryProviderMock(): CountryProviderInterface
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
            'EUR/GBP' => 0.9,
            'GBP/EUR' => 1.111111,
            'USD/GBP' => 0.72,
            'GBP/USD' => 1.388889,
        ]), self::DEFAULT_CURRENCY);
    }

    /**
     * Returns a currency repository mock.
     *
     * @return CurrencyRepositoryInterface|MockObject
     */
    protected function getCurrencyRepositoryMock(): CurrencyRepositoryInterface
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
            ->with(Fixture::CURRENCY_EUR)
            ->willReturn(Fixture::currency(Fixture::CURRENCY_EUR));

        $mock
            ->method('findOneByCode')
            ->with(Fixture::CURRENCY_USD)
            ->willReturn(Fixture::currency(Fixture::CURRENCY_USD));

        return $mock;
    }

    /**
     * Returns a customer group repository mock.
     *
     * @return CustomerGroupRepositoryInterface|MockObject
     */
    protected function getCustomerGroupRepositoryMock(): CustomerGroupRepositoryInterface
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
     *
     * @return DiscountResolverInterface|MockObject
     */
    protected function getDiscountResolverMock(): DiscountResolverInterface
    {
        return $this->mockService(DiscountResolverInterface::class);
    }

    /**
     * Returns the event dispatcher mock.
     *
     * @return EventDispatcherInterface|MockObject
     */
    protected function getEventDispatcherMock(): EventDispatcherInterface
    {
        return $this->mockService(EventDispatcherInterface::class);
    }

    /**
     * Returns the entity manager mock.
     *
     * @return EntityManagerInterface|MockObject
     *
     * @TODO break dependency
     */
    protected function getEntityManagerMock(): EntityManagerInterface
    {
        return $this->mockService(EntityManagerInterface::class);
    }

    /**
     * Returns a payment method mock.
     *
     * @param int $type
     *
     * @return PaymentMethodInterface|MockObject
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

    /**
     * Returns the persistence helper mock.
     *
     * @return PersistenceHelperInterface|MockObject
     */
    protected function getPersistenceHelperMock(): PersistenceHelperInterface
    {
        return $this->mockService(PersistenceHelperInterface::class);
    }

    /**
     * Returns the purchase cost guesser mock.
     *
     * @return PurchaseCostGuesserInterface|MockObject
     */
    protected function getPurchaseCostGuesserMock(): PurchaseCostGuesserInterface
    {
        return $this->mockService(PurchaseCostGuesserInterface::class);
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
     * Returns the shipment address resolver mock.
     *
     * @return ShipmentAddressResolverInterface|MockObject
     */
    protected function getShipmentAddressResolverMock(): ShipmentAddressResolverInterface
    {
        return $this->mockService(ShipmentAddressResolverInterface::class);
    }

    /**
     * Returns the (shipment) weight calculator mock.
     *
     * @return ShipmentWeightCalculator|MockObject
     */
    protected function getShipmentWeightCalculatorMock(): ShipmentWeightCalculator
    {
        return $this->mockService(ShipmentWeightCalculator::class);
    }

    /**
     * Returns the shipment price resolver mock.
     *
     * @return ShipmentPriceResolverInterface|MockObject
     */
    protected function getShipmentPriceResolverMock(): ShipmentPriceResolverInterface
    {
        return $this->mockService(ShipmentPriceResolverInterface::class);
    }

    /**
     * Returns the stock unit repository mock.
     *
     * @return StockUnitRepositoryInterface|MockObject
     */
    protected function getStockUnitRepositoryMock(): StockUnitRepositoryInterface
    {
        return $this->mockService(StockUnitRepositoryInterface::class);
    }

    /**
     * Returns the subject helper mock.
     *
     * @return SubjectHelperInterface|MockObject
     */
    protected function getSubjectHelperMock(): SubjectHelperInterface
    {
        return $this->mockService(SubjectHelperInterface::class);
    }

    /**
     * Returns the supplier order item repository mock.
     *
     * @return SupplierOrderItemRepositoryInterface|MockObject
     */
    protected function getSupplierOrderItemRepositoryMock(): SupplierOrderItemRepositoryInterface
    {
        return $this->mockService(SupplierOrderItemRepositoryInterface::class);
    }

    /**
     * Returns the supplier product repository mock.
     *
     * @return SupplierProductRepositoryInterface|MockObject
     */
    protected function getSupplierProductRepositoryMock(): SupplierProductRepositoryInterface
    {
        return $this->mockService(SupplierProductRepositoryInterface::class);
    }

    /**
     * Returns the tax resolver mock.
     *
     * @return TaxResolverInterface|MockObject
     */
    protected function getTaxResolverMock(): TaxResolverInterface
    {
        return $this->mockService(TaxResolverInterface::class);
    }

    /**
     * Returns a tax rule repository mock.
     *
     * @return TaxRuleRepositoryInterface|MockObject
     */
    protected function getTaxRuleRepositoryMock(): TaxRuleRepositoryInterface
    {
        return $this->mockService(TaxRuleRepositoryInterface::class);
    }

    /**
     * Returns the warehouse provider mock.
     *
     * @return WarehouseProviderInterface|MockObject
     */
    protected function getWarehouseProviderMock(): WarehouseProviderInterface
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
            ->expects($options['order'] ?? $this->once())
            ->method($options['remove'] ? 'remove' : 'persistAndRecompute')
            ->with(...$arguments);
    }
}
