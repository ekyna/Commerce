<?php

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
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
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContextProvider
 * @package Ekyna\Component\Commerce\Common\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextProvider implements ContextProviderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var LocaleProviderInterface
     */
    protected $localProvider;

    /**
     * @var CurrencyProviderInterface
     */
    protected $currencyProvider;

    /**
     * @var CountryProviderInterface
     */
    protected $countryProvider;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var string
     */
    protected $defaultVatDisplayMode;

    /**
     * @var string
     */
    protected $contextClass;

    /**
     * @var ContextInterface
     */
    protected $context;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface         $eventDispatcher
     * @param CartProviderInterface            $cartProvider
     * @param CustomerProviderInterface        $customerProvider
     * @param LocaleProviderInterface          $localProvider
     * @param CurrencyProviderInterface        $currencyProvider
     * @param CountryProviderInterface         $countryProvider
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param string                           $defaultVatDisplayMode
     * @param string                           $contextClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider,
        LocaleProviderInterface $localProvider,
        CurrencyProviderInterface $currencyProvider,
        CountryProviderInterface $countryProvider,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        $defaultVatDisplayMode = VatDisplayModes::MODE_ATI,
        $contextClass = Context::class
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->customerProvider = $customerProvider;
        $this->cartProvider = $cartProvider;
        $this->localProvider = $localProvider;
        $this->currencyProvider = $currencyProvider;
        $this->countryProvider = $countryProvider;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->defaultVatDisplayMode = $defaultVatDisplayMode;
        $this->contextClass = $contextClass;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerProvider(): CustomerProviderInterface
    {
        return $this->customerProvider;
    }

    /**
     * @inheritDoc
     */
    public function getCartProvider(): CartProviderInterface
    {
        return $this->cartProvider;
    }

    /**
     * @inheritDoc
     */
    public function getLocalProvider(): LocaleProviderInterface
    {
        return $this->localProvider;
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyProvider(): CurrencyProviderInterface
    {
        return $this->currencyProvider;
    }

    /**
     * @inheritDoc
     */
    public function getCountryProvider(): CountryProviderInterface
    {
        return $this->countryProvider;
    }

    /**
     * @inheritDoc
     */
    public function getContext(SaleInterface $sale = null): ContextInterface
    {
        if ($sale) {
            // TODO Check if up to date
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
                "Expected instance of " . ContextInterface::class . " or " . SaleInterface::class
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function clearContext(): ContextProviderInterface
    {
        $this->context = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function changeCurrency($currency): ContextProviderInterface
    {
        $this->clearContext();

        if (is_string($currency)) {
            $currency = $this->currencyProvider->getCurrency($currency);
        }

        if (!$currency instanceof CurrencyInterface) {
            throw new UnexpectedValueException("Expected string or instance of " . CurrencyInterface::class);
        }

        $this->currencyProvider->setCurrentCurrency($currency->getCode());

        // TODO Dispatch and use event

        // Update cart currency
        if ($this->cartProvider->hasCart()) {
            $cart = $this->cartProvider->getCart();

            if (!$cart->isLocked()) {
                if ($cart->getCurrency() !== $currency) {
                    $cart->setCurrency($currency);
                    $this->cartProvider->saveCart();
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function changeCountry($country): ContextProviderInterface
    {
        $this->clearContext();

        $country = $country instanceof CountryInterface ? $country->getCode() : $country;

        $this->countryProvider->setCurrentCountry($country);

        return $this;
    }

    /**
     * Creates and sets the sale context.
     *
     * @param SaleInterface $sale The sale
     *
     * @return ContextInterface
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
     *
     * @return ContextInterface
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
     *
     * @param ContextInterface  $context
     * @param CustomerInterface $customer
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
     *
     * @param ContextInterface $context
     *
     * @return ContextInterface
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

        $this->eventDispatcher->dispatch(ContextEvents::BUILD, new ContextEvent($context));

        return $context;
    }

    /**
     * Creates a new context.
     *
     * @return ContextInterface
     */
    protected function createContext(): ContextInterface
    {
        return new $this->contextClass;
    }
}
