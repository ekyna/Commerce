<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
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
     * @var \Doctrine\ORM\Query
     */
    private $byMethodAndStatesFromDateQuery;


    /**
     * @inheritDoc
     */
    public function findOneByKey($key)
    {
        return $this->findOneBy(['key' => $key]);
    }

    /**
     * @inheritDoc
     */
    public function findByMethodAndStates(PaymentMethodInterface $method, array $states, \DateTime $fromDate = null)
    {
        foreach ($states as $state) {
            PaymentStates::isValidState($state);
        }

        if (null === $fromDate) {
            $fromDate = new \DateTime();
            $fromDate->modify('-200 years'); // At least ! xD
        }

        return $this
            ->getByMethodAndStatesFromDateQuery()
            ->setParameter('method', $method)
            ->setParameter('date', $fromDate, Type::DATE)
            ->setParameter('states', $states)
            ->getResult();
    }

    /**
     * Returns the "find by method and states from date" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByMethodAndStatesFromDateQuery()
    {
        if (null !== $this->byMethodAndStatesFromDateQuery) {
            return $this->byMethodAndStatesFromDateQuery;
        }

        $qb = $this->createQueryBuilder('p');

        $query = $qb
            ->andWhere($qb->expr()->eq('p.method', ':method'))
            ->andWhere($qb->expr()->in('p.state', ':states'))
            ->andWhere($qb->expr()->gte('p.createdAt', ':date'))
            ->addOrderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->useQueryCache(true);

        return $this->byMethodAndStatesFromDateQuery = $query;
    }
}
