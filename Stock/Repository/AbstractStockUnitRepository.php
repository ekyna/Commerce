<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Model\DateRange;

use function array_push;
use function reset;

/**
 * Class AbstractStockUnitRepository
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    public function findNewBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
        ]);
    }

    public function findPendingBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
        ]);
    }

    public function findReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_READY,
        ]);
    }

    public function findPendingOrReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    public function findNotClosedBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    private ?Query $adjustmentsBySubject             = null;
    private ?Query $adjustmentsBySubjectAndDateRange = null;

    public function findAssignableBySubject(StockSubjectInterface $subject): array
    {
        $this->assertSubject($subject);

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->isNull($alias . '.supplierOrderItem'), // Not linked to a supplier order
                        $qb->expr()->isNull($alias . '.productionOrder'),   // Not linked to a production order
                        $qb->expr()->eq($alias . '.adjustedQuantity', 0)    // Not adjusted
                    ),
                    $qb->expr()->lt(                                    // Sold lower than ordered + adjusted
                        $alias . '.soldQuantity',
                        $qb->expr()->sum($alias . '.orderedQuantity', $alias . '.adjustedQuantity')
                    )
                )
            )
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    public function findLinkableBySubject(StockSubjectInterface $subject): array
    {
        $this->assertSubject($subject);

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->neq($alias . '.state', ':state'))      // Not closed
            ->andWhere($qb->expr()->isNull($alias . '.supplierOrderItem')) // Not linked to a supplier order
            ->andWhere($qb->expr()->isNull($alias . '.productionOrder'))   // Not linked to a production order
            ->andWhere($qb->expr()->eq($alias . '.adjustedQuantity', 0))   // Not adjusted
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findInStock(): array
    {
        $qb = $this->getQueryBuilder('psu');

        $inStock = $qb->expr()->diff(
            $qb->expr()->sum('psu.receivedQuantity', 'psu.adjustedQuantity'),
            'psu.shippedQuantity'
        );

        return $qb
            ->join('psu.product', 'p')
            ->andWhere($qb->expr()->gt($inStock, 0))
            ->getQuery()
            ->getResult();
    }

    public function findLatestNotClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
    {
        $this->assertSubject($subject);

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

    public function findLatestClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
    {
        $this->assertSubject($subject);

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

    public function findAdjustmentsBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range): array
    {
        if ($range) {
            $result = $this
                ->getAdjustmentsBySubjectAndDateRangeQuery()
                ->setParameter('product', $subject)
                ->setParameter('start', $range->getStart(), Types::DATETIME_MUTABLE)
                ->setParameter('end', $range->getEnd(), Types::DATETIME_MUTABLE)
                ->getResult();
        } else {
            $result = $this
                ->getAdjustmentsBySubjectQuery()
                ->setParameter('product', $subject)
                ->getResult();
        }

        return $this->selectAdjustments($result, $range);
    }

    /**
     * @param array<int, StockUnitInterface> $units
     * @return array<int, StockAdjustmentInterface>
     */
    private function selectAdjustments(array $units, ?DateRange $range): array
    {
        $adjustments = [];

        if (null === $range) {
            foreach ($units as $unit) {
                array_push($adjustments, ...$unit->getStockAdjustments()->toArray());
            }

            return $adjustments;
        }

        $filter = (static function (StockAdjustmentInterface $adjustment) use ($range): bool {
            return $range->getStart() <= $adjustment->getCreatedAt()
                && $range->getEnd() >= $adjustment->getCreatedAt();
        })(...);

        foreach ($units as $unit) {
            array_push($adjustments, ...$unit->getStockAdjustments()->filter($filter));
        }

        return $adjustments;
    }

    private function getAdjustmentsBySubjectQuery(): Query
    {
        if (null !== $this->adjustmentsBySubject) {
            return $this->adjustmentsBySubject;
        }

        $qb = $this->createQueryBuilder('u');
        $ex = $qb->expr();

        return $this->adjustmentsBySubject = $qb
            ->addSelect('a')
            ->join('u.stockAdjustments', 'a')
            ->where($ex->eq('u.product', ':product'))
            ->getQuery();
    }

    private function getAdjustmentsBySubjectAndDateRangeQuery(): Query
    {
        if (null !== $this->adjustmentsBySubjectAndDateRange) {
            return $this->adjustmentsBySubjectAndDateRange;
        }

        $qb = $this->createQueryBuilder('u');
        $ex = $qb->expr();

        return $this->adjustmentsBySubjectAndDateRange = $qb
            ->addSelect('a')
            ->join('u.stockAdjustments', 'a')
            ->where($ex->eq('u.product', ':product'))
            ->andWhere($ex->between('a.createdAt', ':start', ':end'))
            ->getQuery();
    }


    /**
     * Finds stock units by subject and states.
     *
     * @return array<int, StockUnitInterface>
     */
    protected function findBySubjectAndStates(StockSubjectInterface $subject, array $states, string $sort = 'ASC'): array
    {
        $this->assertSubject($subject);

        if (empty($states)) {
            throw new InvalidArgumentException('Expected at least one state.');
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
            ->addOrderBy($alias . '.createdAt', $sort)
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    abstract protected function assertSubject(StockSubjectInterface $subject): void;
}
