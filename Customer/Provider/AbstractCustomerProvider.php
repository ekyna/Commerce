<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Provider;

use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class AbstractCustomerProvider
 * @package Ekyna\Component\Commerce\Customer\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCustomerProvider implements CustomerProviderInterface
{
    protected CustomerGroupRepositoryInterface $customerGroupRepository;

    protected ?CustomerInterface $customer = null;


    public function __construct(CustomerGroupRepositoryInterface $customerGroupRepository)
    {
        $this->customerGroupRepository = $customerGroupRepository;
    }

    public function hasCustomer(): bool
    {
        return null !== $this->customer;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function getCustomerGroup(): CustomerGroupInterface
    {
        if ($this->hasCustomer()) {
            return $this->getCustomer()->getCustomerGroup();
        }

        return $this->customerGroupRepository->findDefault();
    }

    public function reset(): void
    {
        $this->customer = null;
    }

    public function clear(): void
    {
        $this->customer = null;
    }
}
