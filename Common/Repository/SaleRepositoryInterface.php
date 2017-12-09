<?php

namespace Ekyna\Component\Commerce\Common\Repository;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface SaleRepositoryInterface
 * @package Ekyna\Component\Commerce\Common\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleRepositoryInterface extends ResourceRepositoryInterface
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
     * @param bool              $withChildren
     *
     * @return array|SaleInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, array $states = [], $withChildren = false);

    /**
     * Finds the sale by customer and number.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return SaleInterface|null
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number);

    /**
     * Finds the sales by subject, optionally filtered by states.
     *
     * @param SubjectInterface $subject
     * @param array            $states
     *
     * @return array|SaleInterface[]
     */
    public function findBySubject(SubjectInterface $subject, array $states = []);
}
