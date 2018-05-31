<?php

namespace Ekyna\Component\Commerce\Common\Context;

use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Class ContextProvider
 * @package Ekyna\Component\Commerce\Common\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextProvider implements ContextProviderInterface
{
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
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    protected $currencyRepository;

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
     * @param CartProviderInterface            $cartProvider
     * @param CustomerProviderInterface        $customerProvider
     * @param LocaleProviderInterface          $localProvider
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CountryRepositoryInterface       $countryRepository
     * @param CurrencyRepositoryInterface      $currencyRepository
     * @param string                           $defaultVatDisplayMode
     * @param string                           $contextClass
     */
    public function __construct(
        CartProviderInterface $cartProvider,
        CustomerProviderInterface $customerProvider,
        LocaleProviderInterface $localProvider,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CountryRepositoryInterface $countryRepository,
        CurrencyRepositoryInterface $currencyRepository,
        $defaultVatDisplayMode = VatDisplayModes::MODE_ATI,
        $contextClass = Context::class
    ) {
        $this->customerProvider = $customerProvider;
        $this->cartProvider = $cartProvider;
        $this->localProvider = $localProvider;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
        $this->defaultVatDisplayMode = $defaultVatDisplayMode;
        $this->contextClass = $contextClass;
    }

    /**
     * @inheritdoc
     */
    public function getContext(SaleInterface $sale = null, $fallback = true)
    {
        if ($sale) {
            // TODO Check
            if (null !== $context = $sale->getContext()) {
                return $context;
            }

            return $this->createSaleContext($sale, $fallback);
        }

        if (null !== $this->context) {
            return $this->context;
        }

        if ($this->cartProvider->hasCart()) {
            return $this->context = $this->createSaleContext(
                $this->cartProvider->getCart(),
                $fallback
            );
        }

        return $this->context = $this->createDefaultContext($fallback);
    }

    /**
     * Creates and sets the sale context.
     *
     * @param SaleInterface $sale     The sale
     * @param bool          $fallback Whether to fallback to logged in customer.
     *
     * @return ContextInterface
     */
    protected function createSaleContext(SaleInterface $sale, $fallback = true)
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
        if (null !== $address = $sale->getDeliveryAddress()) {
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
        } elseif ($fallback && $this->customerProvider->hasCustomer()) {
            $this->fillFromCustomer($context, $this->customerProvider->getCustomer());
        }

        $this->finalize($context);

        $sale->setContext($context);

        return $context;
    }

    /**
     * Creates a default context.
     *
     * @param bool $fallback Whether to fallback to logged in customer.
     *
     * @return ContextInterface
     */
    protected function createDefaultContext($fallback = true)
    {
        $context = $this->createContext();

        if ($fallback && $this->customerProvider->hasCustomer()) {
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
    protected function fillFromCustomer(ContextInterface $context, CustomerInterface $customer)
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
        /* TODO (?) if (null === $context->getCurrency()) {
            $context->setCurrency($customer->getDefaultCurrency());
        }
        if (null === $context->getLocale()) {
            $context->setLocale($customer->getDefaultLocale());
        }*/
    }

    /**
     * Fills the context's empty properties with default values.
     *
     * @param ContextInterface $context
     */
    protected function finalize(ContextInterface $context)
    {
        if (null === $context->getCustomerGroup()) {
            $context->setCustomerGroup($this->customerGroupRepository->findDefault());
        }
        if (null === $context->getInvoiceCountry()) {
            $context->setInvoiceCountry($this->countryRepository->findDefault());
        }
        if (null === $context->getDeliveryCountry()) {
            $context->setDeliveryCountry($this->countryRepository->findDefault());
        }
        if (null === $context->getCurrency()) {
            $context->setCurrency($this->currencyRepository->findDefault());
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
    }

    /**
     * Creates a new context.
     *
     * @return ContextInterface
     */
    protected function createContext()
    {
        return new $this->contextClass;
    }
}
