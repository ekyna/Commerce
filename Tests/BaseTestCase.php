<?php

namespace Ekyna\Component\Commerce\Tests;

use Ekyna\Component\Commerce\Common\Entity\Currency;
use Ekyna\Component\Commerce\Common\Factory\SaleFactory;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Entity\CustomerGroup;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class BaseTestCase
 * @package Ekyna\Component\Commerce\Tests
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerGroupInterface[]
     */
    private $customerGroups;

    /**
     * @var CurrencyInterface[]
     */
    private $currencies;


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
            ->setEnabled(true)
            ->setDefault(true);

        $dollar = new Currency();
        $dollar
            ->setName('US Dollar')
            ->setCode('USD')
            ->setEnabled(true)
            ->setDefault(false);


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
     * @return \PHPUnit_Framework_MockObject_MockObject|CustomerGroupRepositoryInterface
     */
    protected function createCustomerGroupRepositoryMock()
    {
        $mock = $this->createMock(CustomerGroupRepositoryInterface::class);

        $mock->method('findDefault')->willReturn($this->getDefaultCustomerGroup());

        return $mock;
    }

    /**
     * Returns a currency repository mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|CurrencyRepositoryInterface
     */
    protected function createCurrencyRepositoryMock()
    {
        $mock = $this->createMock(CurrencyRepositoryInterface::class);

        $mock->method('findDefault')->willReturn($this->getDefaultCurrency());
        $mock->method('findOneByCode')->with('EUR')->willReturn($this->getCurrencyByCode('EUR'));
        $mock->method('findOneByCode')->with('USD')->willReturn($this->getCurrencyByCode('USD'));

        return $mock;
    }

    /**
     * Returns a sale factory.
     *
     * @return SaleFactory
     */
    protected function createSaleFactory()
    {
        return new SaleFactory(
            $this->createCustomerGroupRepositoryMock(),
            $this->createCurrencyRepositoryMock()
        );
    }
}
