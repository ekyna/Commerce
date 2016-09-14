<?php

namespace Ekyna\Component\Commerce\Cart\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;

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
     * @var ObjectManager
     */
    protected $cartManager;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

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
     * Sets the cart manager.
     *
     * @param ObjectManager $manager
     */
    public function setCartManager(ObjectManager $manager)
    {
        $this->cartManager = $manager;
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
     * Sets the currency repository.
     *
     * @param CurrencyRepositoryInterface $repository
     */
    public function setCurrencyRepository(CurrencyRepositoryInterface $repository)
    {
        $this->currencyRepository = $repository;
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

        $this->cartManager->persist($this->cart);
        $this->cartManager->flush();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearCart()
    {
        if ($this->hasCart()) {
            $this->cartManager->remove($this->cart);
            $this->cartManager->flush();
        }

        $this->cart = null;

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
        }

        // Sets the currency
        if (null === $cart->getCurrency()) {
            $cart->setCurrency($this->currencyRepository->findDefault());
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
