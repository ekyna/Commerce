<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Commerce\Support\Repository\TicketRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TicketRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketRepository extends ResourceRepository implements TicketRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOpened(int $limit = 4)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->andWhere($qb->expr()->eq('t.state', ':state'))
            ->addOrderBy('t.updatedAt', 'ASC')
            ->getQuery()
            ->setParameters([
                'state' => TicketStates::STATE_OPENED,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->andWhere($qb->expr()->eq('t.customer', ':customer'))
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByOrder(OrderInterface $order)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->andWhere($qb->expr()->isMemberOf(':order', 't.orders'))
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'order' => $order,
            ])
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByQuote(QuoteInterface $quote)
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->andWhere($qb->expr()->isMemberOf(':quote', 't.quotes'))
            ->addOrderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'quote' => $quote,
            ])
            ->getResult();
    }
}
