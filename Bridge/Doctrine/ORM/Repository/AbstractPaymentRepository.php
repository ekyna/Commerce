<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Types;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class AbstractPaymentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractPaymentRepository extends ResourceRepository implements PaymentRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByKey(string $key): ?PaymentInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findOneBy(['key' => $key]);
    }

    /**
     * @inheritDoc
     */
    public function findByMethodAndStates(
        PaymentMethodInterface $method,
        array $states,
        bool $filter = null,
        \DateTime $fromDate = null
    ): array {
        foreach ($states as $state) {
            PaymentStates::isValidState($state);
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.method', ':method'))
            ->andWhere($qb->expr()->in('p.state', ':states'))
            ->setParameter('method', $method)
            ->setParameter('states', $states)
            ->addOrderBy('p.createdAt', 'ASC');

        if (!is_null($filter)) {
            $qb
                ->andWhere($qb->expr()->eq('p.refund', ':refund'))
                ->setParameter('refund', !$filter);
        }

        if (!is_null($fromDate)) {
            $qb
                ->andWhere($qb->expr()->gte('p.createdAt', ':date'))
                ->setParameter('date', $fromDate, Types::DATE_MUTABLE);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByMonth(\DateTime $date, array $states): array
    {
        foreach ($states as $state) {
            PaymentStates::isValidState($state);
        }

        $qb = $this->createQueryBuilder('p');

        $start = clone $date;
        $start->modify('first day of this month');
        $start->setTime(0, 0, 0, 0);

        $end = clone $date;
        $end->modify('last day of this month');
        $end->setTime(23, 59, 59, 999999);

        return $qb
            ->andWhere($qb->expr()->between('p.completedAt', ':start', ':end'))
            ->andWhere($qb->expr()->in('p.state', ':states'))
            ->addOrderBy('p.completedAt', 'ASC')
            ->getQuery()
            ->setParameter('start', $start, Types::DATETIME_MUTABLE)
            ->setParameter('end', $end, Types::DATETIME_MUTABLE)
            ->setParameter('states', $states)
            ->getResult();
    }
}
