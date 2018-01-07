<?php

namespace Ekyna\Component\Commerce\Tests;

use Ekyna\Component\Commerce\Common\Converter\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRepositoryMock;

    /**
     * @var CurrencyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var PersistenceHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceHelper;

    /**
     * @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var SubjectHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectHelper;

    /**
     * Returns a customer group repository mock.
     *
     * @return CustomerGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCustomerGroupRepositoryMock()
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
     * @return CurrencyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCurrencyRepositoryMock()
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
    protected function getCurrencyConverter()
    {
        if (null !== $this->currencyConverter) {
            return $this->currencyConverter;
        }

        return $this->currencyConverter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.0,
            'USD/EUR' => 1.0,
        ], 'EUR');
    }

    /**
     * Returns the tax resolver mock.
     *
     * @return TaxResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTaxResolverMock()
    {
        if (null !== $this->taxResolver) {
            return $this->taxResolver;
        }

        return $this->taxResolver = $this->createMock(TaxResolverInterface::class);
    }

    /**
     * Returns the discount resolver mock.
     *
     * @return DiscountResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDiscountResolverMock()
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
    protected function getSaleFactory()
    {
        if (null !== $this->saleFactory) {
            return $this->saleFactory;
        }

        return $this->saleFactory = new SaleFactory(
            $this->getCustomerGroupRepositoryMock(),
            $this->getCurrencyRepositoryMock()
        );
    }

    /**
     * Returns the persistence helper mock.
     *
     * @return PersistenceHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceHelperMock()
    {
        if (null !== $this->persistenceHelper) {
            return $this->persistenceHelper;
        }

        return $this->persistenceHelper = $this->createMock(PersistenceHelperInterface::class);
    }

    /**
     * Returns the event dispatcher mock.
     *
     * @return EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock()
    {
        if (null !== $this->eventDispatcher) {
            return $this->eventDispatcher;
        }

        return $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    /**
     * Returns the subject helper mock.
     *
     * @return SubjectHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSubjectHelperMock()
    {
        if (null !== $this->subjectHelper) {
            return $this->subjectHelper;
        }

        return $this->subjectHelper = $this->createMock(SubjectHelperInterface::class);
    }
}
