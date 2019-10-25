<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository implements CustomerRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByNumber(string $number): ?CustomerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findOneBy(['number' => $number]);
    }
}
