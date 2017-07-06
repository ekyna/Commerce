<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Interface SaleRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleRepositoryInterface
{
    /**
     * Finds the sale by its id.
     *
     * @param int $id
     *
     * @return SaleInterface|null
     */
    public function findOneById($id);

    /**
     * Finds the sale by its key.
     *
     * @param string $key
     *
     * @return SaleInterface|null
     */
    public function findOneByKey($key);

    /**
     * Finds the sales by customer, optionally filtered by states.
     *
     * @param CustomerInterface $customer
     * @param array             $states
     *
     * @return array|SaleInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, array $states = []);

    /**
     * Finds the sale by customer and number.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return SaleInterface|null
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number);
}
