<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

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
    public function findOneByKey(string $key): ?CustomerInterface
    {
        return $this->findOneBy(['key' => $key]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByNumber(string $number): ?CustomerInterface
    {
        return $this->findOneBy(['number' => $number]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByEmail(string $email): ?CustomerInterface
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @inheritDoc
     */
    public function findWithBirthdayToday(): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->eq('DAY(c.birthday)', ':day'))
            ->andWhere($qb->expr()->eq('MONTH(c.birthday)', ':month'))
            ->getQuery()
            ->setParameters([
                'day'   => date('j'),
                'month' => date('n'),
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findWithLoyaltyPoints(int $points): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->gte('c.loyaltyPoints', ':points'))
            ->getQuery()
            ->setParameter('points', $points)
            ->getResult();
    }
}
