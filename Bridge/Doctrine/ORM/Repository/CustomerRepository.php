<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Sets the groupRepository.
     *
     * @param CustomerGroupRepositoryInterface $groupRepository
     */
    public function setGroupRepository($groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritDoc
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerInterface
     */
    public function createNew()
    {
        $class = $this->getClassName();

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = new $class;

        if ($this->groupRepository) {
            $customer->setCustomerGroup($this->groupRepository->findDefault());
        }

        return $customer;
    }
}
