<?php

namespace Ekyna\Component\Commerce\Tests;

use Ekyna\Component\Commerce\Common\Converter\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroup;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class BaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerGroupInterface[]
     */
    private $customerGroups; # TODO move to fixtures

    /**
     * @var CustomerGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRepositoryMock;

    /**
     * @var CurrencyInterface[]
     */
    private $currencies; # TODO move to fixtures

    /**
     * @var CurrencyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyRepositoryMock;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var PersistenceHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceHelper;


    /**
     * Returns the customer groups.
     *
     * @return array|CustomerGroupInterface[]
     */
    protected function getCustomerGroups()
    {
        if (null !== $this->customerGroups) {
            return $this->customerGroups;
        }

        $customers = new CustomerGroup();
        $customers
            ->setName('Customers')
            ->setDefault(true);

        $resellers = new CustomerGroup();
        $resellers
            ->setName('Resellers')
            ->setDefault(false);

        return $this->customerGroups = [$customers, $resellers];
    }

    /**
     * Returns the currencies.
     *
     * @return array|CurrencyInterface[]
     */
    protected function getCurrencies()
    {
        if (null !== $this->currencies) {
            return $this->currencies;
        }

        $euro = new Currency();
        $euro
            ->setName('Euro')
            ->setCode('EUR')
            ->setEnabled(true);

        $dollar = new Currency();
        $dollar
            ->setName('US Dollar')
            ->setCode('USD')
            ->setEnabled(true);


        return $this->currencies = [$euro, $dollar];
    }

    /**
     * Returns the default currency.
     *
     * @return CurrencyInterface
     */
    protected function getDefaultCurrency()
    {
        return $this->getCurrencies()[0];
    }

    /**
     * Finds the currency by its code.
     *
     * @param string $code
     *
     * @return CurrencyInterface
     */
    protected function getCurrencyByCode($code)
    {
        foreach ($this->getCurrencies() as $currency) {
            if ($currency->getCode() === $code) {
                return $currency;
            }
        }

        throw new \InvalidArgumentException("Unexpected currency code '$code'.");
    }

    /**
     * Returns the default customer group.
     *
     * @return CustomerGroupInterface
     */
    protected function getDefaultCustomerGroup()
    {
        return $this->getCustomerGroups()[0];
    }

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

        $mock->method('findDefault')->willReturn($this->getDefaultCustomerGroup());

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

        $mock->method('findDefault')->willReturn($this->getDefaultCurrency());
        $mock->method('findOneByCode')->with('EUR')->willReturn($this->getCurrencyByCode('EUR'));
        $mock->method('findOneByCode')->with('USD')->willReturn($this->getCurrencyByCode('USD'));

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
     * Returns the persistence helper.
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
}
