<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Customer\Entity\LoyaltyLog;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\LoyaltyLogRepositoryInterface;

/**
 * Class LoyaltyLogRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyLogRepository extends ServiceEntityRepository implements LoyaltyLogRepositoryInterface
{
    /**
     * LoyaltyLogRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyLog::class);
    }

    /**
     * @inheritDoc
     */
    public function findByCustomerAndOrigin(CustomerInterface $customer, string $origin): ?LoyaltyLog
    {
        return $this->findOneBy([
            'customer' => $customer,
            'origin'   => $origin,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer): array
    {
        return $this->findBy(
            ['customer' => $customer],
            ['createdAt' => 'DESC']
        );
    }
}
