<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory;

use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Factory\CustomerFactoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class CustomerFactory
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerFactory extends ResourceFactory implements CustomerFactoryInterface
{
    protected CurrencyProviderInterface        $currencyProvider;
    protected LocaleProviderInterface          $localeProvider;
    protected CustomerGroupRepositoryInterface $customerGroupRepository;


    public function __construct(
        CurrencyProviderInterface        $currencyProvider,
        LocaleProviderInterface          $localeProvider,
        CustomerGroupRepositoryInterface $customerGroupRepository
    ) {
        $this->currencyProvider = $currencyProvider;
        $this->localeProvider = $localeProvider;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * @return CustomerInterface
     */
    public function create(): ResourceInterface
    {
        /** @var CustomerInterface $customer */
        $customer = parent::create();

        if (null === $customer->getCurrency()) {
            $customer->setCurrency($this->currencyProvider->getCurrency());
        }

        if (null === $customer->getLocale()) {
            $customer->setLocale($this->localeProvider->getCurrentLocale());
        }

        $customer->setCustomerGroup(
            $this->customerGroupRepository->findDefault()
        );

        return $customer;
    }
}
