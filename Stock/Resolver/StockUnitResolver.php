<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitResolver
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitResolver implements StockUnitResolverInterface
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var StockUnitCacheInterface
     */
    protected $unitCache;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var array [class => $repository]
     */
    protected $repositoryCache;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface     $subjectHelper
     * @param StockUnitCacheInterface    $unitCache
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        StockUnitCacheInterface $unitCache,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->unitCache = $unitCache;
        $this->persistenceHelper = $persistenceHelper;

        $this->repositoryCache = [];
    }

    /**
     * @inheritdoc
     */
    public function createBySubject(
        StockSubjectInterface $subject,
        StockUnitInterface $exceptStockUnit = null
    ): StockUnitInterface {
        // TODO Cache 'new' stock units created by sales (?)

        $stockUnits = array_filter(
            $this->unitCache->findAddedBySubject($subject),
            function (StockUnitInterface $unit) {
                return $unit->getState() === StockUnitStates::STATE_NEW;
            }
        );

        if (!empty($stockUnits)) {
            foreach ($stockUnits as $stockUnit) {
                if ($stockUnit === $exceptStockUnit) {
                    continue;
                }

                return $stockUnit;
            }
        }

        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $this
            ->getRepositoryBySubject($subject)
            ->createNew();

        $stockUnit->setSubject($subject);

        $this->unitCache->add($stockUnit);

        return $stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative): StockUnitInterface
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($relative);

        return $this->createBySubject($subject);
    }

    /**
     * @inheritdoc
     */
    public function findPending($subjectOrRelative): array
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findPendingBySubject($subject);

        return $this->replaceAndFilter($fetched, $subject, new StateFilter([StockUnitStates::STATE_PENDING]));
    }

    /**
     * @inheritdoc
     */
    public function findReady($subjectOrRelative): array
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findReadyBySubject($subject);

        return $this->replaceAndFilter($fetched, $subject, new StateFilter([StockUnitStates::STATE_READY]));
    }

    /**
     * @inheritdoc
     */
    public function findPendingOrReady($subjectOrRelative): array
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findPendingOrReadyBySubject($subject);

        return $this->replaceAndFilter($fetched, $subject, new StateFilter([
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function findNotClosed($subjectOrRelative): array
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findNotClosedBySubject($subject);

        return $this->replaceAndFilter($fetched, $subject, new StateFilter([
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function findAssignable($subjectOrRelative): array
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findAssignableBySubject($subject);

        $filter = new class implements FilterInterface {
            public function filter(StockUnitInterface $unit): bool
            {
                // - Not linked to a supplier order
                // - Sold lower than ordered
                return is_null($unit->getSupplierOrderItem())
                    || ($unit->getSoldQuantity() < $unit->getOrderedQuantity() + $unit->getAdjustedQuantity());
            }
        };

        return $this->replaceAndFilter($fetched, $subject, $filter);
    }

    /**
     * @inheritdoc
     */
    public function findLinkable($subjectOrRelative): ?StockUnitInterface
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        [$subject, $repository] = $this->getSubjectAndRepository($subjectOrRelative);

        $fetched = $repository->findLinkableBySubject($subject);

        $filter = new class implements FilterInterface {
            /** @inheritDoc */
            public function filter(StockUnitInterface $unit): bool
            {
                // - Not linked to a supplier order
                // - Not closed
                return (null === $unit->getSupplierOrderItem())
                    && ($unit->getState() !== StockUnitStates::STATE_CLOSED);
            }
        };

        $stockUnits = $this->replaceAndFilter($fetched, $subject, $filter);

        if (!empty($stockUnits)) {
            return reset($stockUnits);
        }

        return null;
    }

    /**
     * Replaces fetched units by their cached version, and filters the result.
     *
     * @param StockUnitInterface[]  $fetchedUnits
     * @param StockSubjectInterface $subject
     * @param FilterInterface       $filter
     *
     * @return array
     */
    protected function replaceAndFilter(
        array $fetchedUnits,
        StockSubjectInterface $subject,
        FilterInterface $filter
    ): array {
        $filtered = [];

        $addedUnits = $this->unitCache->findAddedBySubject($subject);
        $removedUnits = $this->unitCache->findRemovedBySubject($subject);

        foreach ($fetchedUnits as $fetchedUnit) {
            foreach ($addedUnits as $cachedUnit) {
                if ($cachedUnit->getId() === $fetchedUnit->getId()) {
                    $filtered[] = $cachedUnit;
                    continue 2; // Found cached version, go to next fetched unit
                }
            }

            foreach ($removedUnits as $cachedUnit) {
                if ($cachedUnit->getId() === $fetchedUnit->getId()) {
                    // Unit has been removed, got to next fetched unit
                    continue 2;
                }
            }

            // Cached unit version not found, use fetched one
            $filtered[] = $fetchedUnit;
        }

        return array_filter($filtered, [$filter, 'filter']);
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array(StockSubjectInterface, StockUnitRepositoryInterface)
     */
    protected function getSubjectAndRepository($subjectOrRelative): array
    {
        if ($subjectOrRelative instanceof SubjectRelativeInterface) {
            $subject = $this->subjectHelper->resolve($subjectOrRelative);
        } elseif ($subjectOrRelative instanceof StockSubjectInterface) {
            $subject = $subjectOrRelative;
        } else {
            throw new InvalidArgumentException(sprintf(
                "Expected instance of '%s' or '%s'.",
                SubjectRelativeInterface::class,
                StockSubjectInterface::class
            ));
        }

        return [$subject, $this->getRepositoryBySubject($subject)];
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface $subject
     *
     * @return StockUnitRepositoryInterface
     */
    protected function getRepositoryBySubject(StockSubjectInterface $subject): StockUnitRepositoryInterface
    {
        $class = $subject::getStockUnitClass();

        if (isset($this->repositoryCache[$class])) {
            return $this->repositoryCache[$class];
        }

        $repository = $this->persistenceHelper->getManager()->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockUnitRepositoryInterface::class);
        }

        return $this->repositoryCache[$class] = $repository;
    }
}
