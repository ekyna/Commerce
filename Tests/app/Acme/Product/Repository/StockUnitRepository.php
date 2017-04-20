<?php

namespace Acme\Product\Repository;

use Acme\Product\Entity\Product;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class StockUnitRepository
 * @package Acme\Product\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findNewBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findPendingBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findPendingOrReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findNotClosedBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findAssignableBySubject(StockSubjectInterface $subject): array
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull($alias . '.supplierOrderItem'), // Not yet linked to a supplier order
                $qb->expr()->lt($alias . '.soldQuantity', $alias . '.orderedQuantity')   // Sold lower than ordered
            ))
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findLinkableBySubject(StockSubjectInterface $subject): array
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->isNull($alias . '.supplierOrderItem')) // Not yet linked to a supplier order
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds stock units by subject and states.
     *
     * @param StockSubjectInterface $subject
     * @param array                 $states
     *
     * @return array
     */
    private function findBySubjectAndStates(StockSubjectInterface $subject, array $states): array
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (empty($states)) {
            throw new \InvalidArgumentException('Expected at least one state.');
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        if (1 == count($states)) {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.state', ':state'))
                ->setParameter('state', reset($states));
        } else {
            $qb
                ->andWhere($qb->expr()->in($alias . '.state', ':states'))
                ->setParameter('states', $states);
        }

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findInStock(): array
    {
        $qb = $this->getQueryBuilder('su');

        return $qb
            ->join('su.product', 'p')
            ->andWhere($qb->expr()->gt('su.receivedQuantity', 0))
            ->andWhere($qb->expr()->gt('su.receivedQuantity', 'su.shippedQuantity'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findLatestNotClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->neq($alias . '.state', ':state'))
            ->addOrderBy($alias . '.createdAt', 'DESC')
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findLatestClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->eq($alias . '.state', ':state'))
            ->addOrderBy($alias . '.closedAt', 'DESC')
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    protected function getAlias(): string
    {
        return 'su';
    }
}
