<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Provider;

use Ekyna\Bundle\CommerceBundle\Factory\CartFactory;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

use function is_null;

/**
 * Class AbstractCartProvider
 * @package Ekyna\Component\Commerce\Cart\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCartProvider implements CartProviderInterface
{
    protected CartFactory               $cartFactory;
    protected CartRepositoryInterface   $cartRepository;
    protected ResourceManagerInterface  $cartManager;
    protected CustomerProviderInterface $customerProvider;
    protected CurrencyProviderInterface $currencyProvider;
    protected LocaleProviderInterface   $localeProvider;

    protected ?CartInterface $cart = null;

    public function __construct(
        CartFactory               $cartFactory,
        CartRepositoryInterface   $cartRepository,
        ResourceManagerInterface  $cartManager,
        CustomerProviderInterface $customerProvider,
        CurrencyProviderInterface $currencyProvider,
        LocaleProviderInterface   $localeProvider
    ) {
        $this->cartFactory = $cartFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManager = $cartManager;
        $this->customerProvider = $customerProvider;
        $this->currencyProvider = $currencyProvider;
        $this->localeProvider = $localeProvider;
    }

    public function hasCart(): bool
    {
        return !is_null($this->cart);
    }

    public function getCart(bool $create = false): ?CartInterface
    {
        if ($create) {
            return $this->createCart();
        }

        return $this->cart;
    }

    public function saveCart(): CartProviderInterface
    {
        if (!$this->hasCart()) {
            throw new RuntimeException('Cart has not been initialized yet.');
        }

        $this->updateCustomerGroupAndCurrency();

        $this->cartManager->save($this->cart);

        return $this;
    }

    public function clearCart(): CartProviderInterface
    {
        if (!$this->hasCart() || $this->cart->isLocked()) {
            return $this;
        }

        if (null !== $this->cart->getId()) {
            $this->cartManager->delete($this->cart, true);
        }

        $this->cart = null;

        return $this;
    }

    public function clearInformation(): CartProviderInterface
    {
        if (!$this->hasCart() || $this->cart->isLocked()) {
            return $this;
        }

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

        return $this;
    }

    public function updateCustomerGroupAndCurrency(): CartProviderInterface
    {
        if (!$this->hasCart() || $this->cart->isLocked()) {
            return $this;
        }

        // Customer group
        if (null === $customer = $this->cart->getCustomer()) {
            $this->cart->setCustomerGroup(null);
        } elseif ($this->cart->getCustomerGroup() !== $customer->getCustomerGroup()) {
            $this->cart->setCustomerGroup($customer->getCustomerGroup());
        }

        // Sets the default customer group
        if (null === $this->cart->getCustomerGroup()) {
            $this->cart->setCustomerGroup($this->customerProvider->getCustomerGroup());
        }

        // Sets the currency
        if (null === $this->cart->getCurrency()) {
            $this->cart->setCurrency($this->currencyProvider->getCurrency());
        }

        // Sets the locale
        if (null === $this->cart->getLocale()) {
            $this->cart->setLocale($this->localeProvider->getCurrentLocale());
        }

        return $this;
    }

    public function createCart(): CartInterface
    {
        if ($this->hasCart()) {
            return $this->cart;
        }

        $this->clearCart();

        // Sets the customer if available
        if ($this->customerProvider->hasCustomer()) {
            $cart = $this->cartFactory->createWithCustomer($this->customerProvider->getCustomer());
        } else {
            $cart = $this->cartFactory->create();
        }

        $this->setCart($cart);

        $this->updateCustomerGroupAndCurrency();

        return $this->cart;
    }

    protected function setCart(CartInterface $cart): CartProviderInterface
    {
        $this->cart = $cart;

        return $this;
    }
}
