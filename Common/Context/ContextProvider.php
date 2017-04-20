<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Event\ContextChangeEvent;
use Ekyna\Component\Commerce\Common\Event\ContextEvent;
use Ekyna\Component\Commerce\Common\Event\ContextEvents;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContextLoader
 * @package Ekyna\Component\Commerce\Common\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextProvider implements ContextProviderInterface
{
    protected EventDispatcherInterface         $eventDispatcher;
    protected CustomerProviderInterface        $customerProvider;
    protected CartProviderInterface            $cartProvider;
    protected LocaleProviderInterface          $localProvider;
    protected CurrencyProviderInterface        $currencyProvider;
    protected CountryProviderInterface         $countryProvider;
    protected WarehouseProviderInterface       $warehouseProvider;
    protected CustomerGroupRepositoryInterface $customerGroupRepository;
    protected string                           $defaultVatDisplayMode;
    protected string                           $contextClass;

    protected ?ContextInterface $context = null;


    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider,
        LocaleProviderInterface $localProvider,
        CurrencyProviderInterface $currencyProvider,
        CountryProviderInterface $countryProvider,
        WarehouseProviderInterface $warehouseProvider,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        string $defaultVatDisplayMode = VatDisplayModes::MODE_ATI,
        string $contextClass = Context::class
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->customerProvider = $customerProvider;
        $this->cartProvider = $cartProvider;
        $this->localProvider = $localProvider;
        $this->currencyProvider = $currencyProvider;
        $this->countryProvider = $countryProvider;
        $this->warehouseProvider = $warehouseProvider;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->defaultVatDisplayMode = $defaultVatDisplayMode;
        $this->contextClass = $contextClass;
    }

    public function getCustomerProvider(): CustomerProviderInterface
    {
        return $this->customerProvider;
    }

    public function getCartProvider(): CartProviderInterface
    {
        return $this->cartProvider;
    }

    public function getLocalProvider(): LocaleProviderInterface
    {
        return $this->localProvider;
    }

    public function getCurrencyProvider(): CurrencyProviderInterface
    {
        return $this->currencyProvider;
    }

    public function getCountryProvider(): CountryProviderInterface
    {
        return $this->countryProvider;
    }

    public function getContext(SaleInterface $sale = null): ContextInterface
    {
        if ($sale) {
            // TODO Check if up to date (compare context date with sale 'updated at' date ?)
            if (null !== $context = $sale->getContext()) {
                return $context;
            }

            return $this->createSaleContext($sale);
        }

        if (null !== $this->context) {
            return $this->context;
        }

        if ($this->cartProvider->hasCart()) {
            return $this->context = $this->createSaleContext($this->cartProvider->getCart());
        }

        return $this->context = $this->createDefaultContext();
    }

    /**
     * @inheritDoc
     */
    public function setContext($contextOrSale): ContextProviderInterface
    {
        if ($contextOrSale instanceof ContextInterface) {
            $this->context = $this->finalize($contextOrSale);
        } elseif ($contextOrSale instanceof SaleInterface) {
            $this->context = $this->createSaleContext($contextOrSale);
        } else {
            throw new InvalidArgumentException(
                'Expected instance of ' . ContextInterface::class . ' or ' . SaleInterface::class
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function changeCurrencyAndCountry(
        $currency = null,
        $country = null,
        string $locale = null
    ): ContextProviderInterface {
        if (!is_null($currency)) {
            if (is_string($currency)) {
                $currency = $this->currencyProvider->getCurrency($currency);
            }
            if (!$currency instanceof CurrencyInterface) {
                throw new UnexpectedValueException('Expected string or instance of ' . CurrencyInterface::class);
            }
            if ($currency === $this->currencyProvider->getCurrency()) {
                $currency = null;
            }
        }

        if (!is_null($country)) {
            if (is_string($country)) {
                $country = $this->countryProvider->getCountry($country);
            }
            if (!$country instanceof CountryInterface) {
                throw new UnexpectedValueException('Expected string or instance of ' . CountryInterface::class);
            }
            if ($country === $this->countryProvider->getCountry()) {
                $country = null;
            }
        }

        if (!is_null($locale)) {
            if (!in_array($locale, $this->localProvider->getAvailableLocales(), true)) {
                throw new UnexpectedValueException("Unexpected locale '$locale'.");
            }
            if ($locale === $this->localProvider->getCurrentLocale()) {
                $locale = null;
            }
        }

        if ($currency || $country || $locale) {
            if ($currency) {
                $this->currencyProvider->setCurrency($currency);
            }
            if ($country) {
                $this->countryProvider->setCountry($country);
            }

            $this->eventDispatcher->dispatch(
                new ContextChangeEvent($currency, $country, $locale),
                ContextEvents::CHANGE
            );
        }

        return $this;
    }

    public function onClear(): void
    {
        $this->context = null;
    }

    /**
     * Creates and sets the sale context.
     */
    protected function createSaleContext(SaleInterface $sale): ContextInterface
    {
        $context = $this->createContext();

        if (null !== $group = $sale->getCustomerGroup()) {
            $context
                ->setCustomerGroup($group)
                ->setBusiness($group->isBusiness());
        }
        if (null !== $address = $sale->getInvoiceAddress()) {
            $context->setInvoiceCountry($address->getCountry());
        }
        $address = $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress();
        if (null !== $address) {
            $context->setDeliveryCountry($address->getCountry());
        }
        if (null !== $currency = $sale->getCurrency()) {
            $context->setCurrency($currency);
        }
        if (null !== $mode = $sale->getVatDisplayMode()) {
            $context->setVatDisplayMode($mode);
        }
        if ($sale instanceof OrderInterface && null !== $date = $sale->getCreatedAt()) {
            $context->setDate($date);
        }

        $context->setTaxExempt($sale->isTaxExempt());

        if (null !== $customer = $sale->getCustomer()) {
            $this->fillFromCustomer($context, $customer);
        } elseif ($this->customerProvider->hasCustomer()) {
            $this->fillFromCustomer($context, $this->customerProvider->getCustomer());
        }

        $this->finalize($context);

        $sale->setContext($context);

        return $context;
    }

    /**
     * Creates a default context.
     */
    protected function createDefaultContext(): ContextInterface
    {
        $context = $this->createContext();

        if ($this->customerProvider->hasCustomer()) {
            $this->fillFromCustomer($context, $this->customerProvider->getCustomer());
        }

        $this->finalize($context);

        return $context;
    }

    /**
     * Fills the context from the given customer.
     */
    protected function fillFromCustomer(ContextInterface $context, CustomerInterface $customer): void
    {
        if (null === $context->getCustomerGroup()) {
            $context->setCustomerGroup($customer->getCustomerGroup());
        }
        if (null === $context->getInvoiceCountry()) {
            if (null !== $address = $customer->getDefaultInvoiceAddress(true)) {
                $context->setInvoiceCountry($address->getCountry());
            }
        }
        if (null === $context->getDeliveryCountry()) {
            if (null !== $address = $customer->getDefaultDeliveryAddress(true)) {
                $context->setDeliveryCountry($address->getCountry());
            }
        }
        /*if (null === $context->getCurrency()) {
            $context->setCurrency($customer->getCurrency());
        }
        if (null === $context->getLocale()) {
            $context->setLocale($customer->getLocale());
        }*/
    }

    /**
     * Fills the context's empty properties with default values.
     */
    protected function finalize(ContextInterface $context): ContextInterface
    {
        if (null === $context->getCustomerGroup()) {
            $context->setCustomerGroup($this->customerGroupRepository->findDefault());
        }

        if (null === $context->getInvoiceCountry()) {
            $context->setInvoiceCountry($this->countryProvider->getCountry());
        }

        if (null === $context->getDeliveryCountry()) {
            $context->setDeliveryCountry($this->countryProvider->getCountry());
        }

        if (null === $context->getShippingCountry()) {
            $context->setShippingCountry(
                $this->warehouseProvider->getWarehouse($context->getDeliveryCountry())->getCountry()
            );
        }

        if (null === $context->getCurrency()) {
            $context->setCurrency($this->currencyProvider->getCurrency());
        }

        if (null === $context->getLocale()) {
            $context->setLocale($this->localProvider->getCurrentLocale());
        }

        if (null === $context->getVatDisplayMode()) {
            if (null !== $mode = $context->getCustomerGroup()->getVatDisplayMode()) {
                $context->setVatDisplayMode($mode);
            } else {
                $context->setVatDisplayMode($this->defaultVatDisplayMode);
            }
        }

        $this->eventDispatcher->dispatch(new ContextEvent($context), ContextEvents::BUILD);

        return $context;
    }

    protected function createContext(): ContextInterface
    {
        return new $this->contextClass();
    }
}
