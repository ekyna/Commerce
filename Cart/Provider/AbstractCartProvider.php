<?php

namespace Ekyna\Component\Commerce\Cart\Provider;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;

/**
 * Class AbstractCartProvider
 * @package Ekyna\Component\Commerce\Cart\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCartProvider implements CartProviderInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ResourceOperatorInterface
     */
    protected $cartOperator;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var CartInterface
     */
    protected $cart;


    /**
     * Sets the cart repository.
     *
     * @param CartRepositoryInterface $repository
     */
    public function setCartRepository(CartRepositoryInterface $repository)
    {
        $this->cartRepository = $repository;
    }

    /**
     * Sets the cart operator.
     *
     * @param ResourceOperatorInterface $operator
     */
    public function setCartOperator(ResourceOperatorInterface $operator)
    {
        $this->cartOperator = $operator;
    }

    /**
     * Sets the sale factory.
     *
     * @param SaleFactoryInterface $saleFactory
     */
    public function setSaleFactory(SaleFactoryInterface $saleFactory)
    {
        $this->saleFactory = $saleFactory;
    }

    /**
     * Sets the customer provider.
     *
     * @param CustomerProviderInterface $provider
     */
    public function setCustomerProvider(CustomerProviderInterface $provider)
    {
        $this->customerProvider = $provider;
    }

    /**
     * @inheritdoc
     */
    public function hasCart()
    {
        return null !== $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function getCart($create = false)
    {
        if ($create) {
            return $this->createCart();
        }

        return $this->cart;
    }

    /**
     * @inheritdoc
     */
    public function saveCart()
    {
        if (!$this->hasCart()) {
            throw new RuntimeException('Cart has not been initialized yet.');
        }

        $this->cartOperator->persist($this->cart);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearCart()
    {
        if ($this->hasCart() && null !== $this->cart->getId()) {
            $this->cartOperator->delete($this->cart, true);
        }

        $this->cart = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearInformation()
    {
        // TODO Prevent clearing if there is a processing payment
        if ($this->hasCart()) {
            $this->cart
                ->setCustomer(null)
                ->setCustomerGroup(null)
                // ->setCurrency(null) TODO Can't be set to null
                ->setEmail(null)
                ->setCompany(null)
                ->setGender(null)
                ->setFirstName(null)
                ->setLastName(null)
                ->setInvoiceAddress(null)
                ->setDeliveryAddress(null)
                ->setSameAddress(true);

            $this->updateCustomerGroupAndCurrency();
        }

        return $this;
    }

    /**
     * Updates the cart customer group and currency.
     *
     * @return $this
     */
    public function updateCustomerGroupAndCurrency()
    {
        if (!$this->hasCart()) {
            return $this;
        }

        // Customer group
        if (null !== $customer = $this->cart->getCustomer()) {
            if ($this->cart->getCustomerGroup() !== $customer->getCustomerGroup()) {
                $this->cart->setCustomerGroup($customer->getCustomerGroup());
            }

            // TODO Currency
        }

        // Sets the default customer group
        if (null === $this->cart->getCustomerGroup()) {
            $this->cart->setCustomerGroup($this->saleFactory->getDefaultCustomerGroup());
        }

        // Sets the currency
        if (null === $this->cart->getCurrency()) {
            $this->cart->setCurrency($this->saleFactory->getDefaultCurrency());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createCart()
    {
        if ($this->hasCart()) {
            return $this->cart;
        }

        $this->clearCart();

        $this->setCart($this->cartRepository->createNew());

        // Sets the customer if available
        if ($this->customerProvider->hasCustomer()) {
            $this->cart->setCustomer($this->customerProvider->getCustomer());
        }

        $this->updateCustomerGroupAndCurrency();

        $this->cartOperator->initialize($this->cart);

        return $this->cart;
    }

    /**
     * Sets the cart.
     *
     * @param CartInterface $cart
     *
     * @return AbstractCartProvider
     */
    protected function setCart(CartInterface $cart)
    {
        $this->cart = $cart;

        return $this;
    }
}
