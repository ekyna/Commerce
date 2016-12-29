<?php

namespace Ekyna\Component\Commerce\Customer\Provider;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class AbstractCustomerProvider
 * @package Ekyna\Component\Commerce\Customer\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCustomerProvider implements CustomerProviderInterface
{
    /**
     * @var CustomerInterface
     */
    protected $customer;


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
     * @inheritdoc
     */
    public function reset()
    {
        $this->customer = null;
    }
}
