<?php

namespace Ekyna\Component\Commerce\Customer\Provider;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class AbstractCustomerProvider
 * @package Ekyna\Component\Commerce\Customer\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCustomerProvider implements CustomerProviderInterface
{
    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var CustomerInterface
     */
    protected $customer;


    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(CustomerGroupRepositoryInterface $customerGroupRepository)
    {
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomer()
    {
        return null !== $this->customer;
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerGroup()
    {
        if ($this->hasCustomer()) {
            return $this->getCustomer()->getCustomerGroup();
        }

        return $this->customerGroupRepository->findDefault();
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->customer = null;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->customer = null;
    }
}
