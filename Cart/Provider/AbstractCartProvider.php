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
    public function getCart()
    {
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

        // TODO this should be done by a listener (on persist)
        // Refresh the "expires at date" time.
        $expiresAt = new \DateTime();
        $expiresAt->modify('+1 month'); // TODO parameter
        $this->cart->setExpiresAt($expiresAt);

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
                ->setEmail(null)
                ->setCompany(null)
                ->setGender(null)
                ->setFirstName(null)
                ->setLastName(null)
                ->setInvoiceAddress(null)
                ->setDeliveryAddress(null)
                ->setSameAddress(true);

            // TODO Default customer group (?)
            // TODO Default currency (?)
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

        /** @noinspection PhpParamsInspection */
        /** @var CartInterface $cart */
        $cart = $this->cartRepository->createNew();

        // Sets the customer if available
        if ($this->customerProvider->hasCustomer()) {
            $cart->setCustomer($this->customerProvider->getCustomer());

            // TODO customer preferred currency
        } else {
            $cart->setCustomerGroup($this->saleFactory->getDefaultCustomerGroup());
        }

        // Sets the currency
        if (null === $cart->getCurrency()) {
            $cart->setCurrency($this->saleFactory->getDefaultCurrency());
        }

        $this->setCart($cart);

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
