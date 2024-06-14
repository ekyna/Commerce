<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

/**
 * Class StockUnitResolver
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitResolver implements StockUnitResolverInterface
{
    protected SubjectHelperInterface $subjectHelper;
    protected StockUnitCacheInterface $unitCache;
    protected RepositoryFactoryInterface $repositoryFactory;
    protected FactoryFactoryInterface $factoryFactory;

    public function __construct(
        SubjectHelperInterface $subjectHelper,
        StockUnitCacheInterface $unitCache,
        RepositoryFactoryInterface $repositoryFactory,
        FactoryFactoryInterface $factoryFactory
    ) {
        $this->subjectHelper     = $subjectHelper;
        $this->unitCache         = $unitCache;
        $this->repositoryFactory = $repositoryFactory;
        $this->factoryFactory = $factoryFactory;
    }

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
            ->getFactoryBySubject($subject)
            ->create();

        $stockUnit->setSubject($subject);

        $this->unitCache->add($stockUnit);

        return $stockUnit;
    }

    public function createBySubjectRelative(SubjectRelativeInterface $relative): StockUnitInterface
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($relative);

        return $this->createBySubject($subject);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
                // - Not closed
                // - Not linked to a supplier order
                // - Sold lower than ordered
                return $unit->getState() !== StockUnitStates::STATE_CLOSED
                    && (
                        (is_null($unit->getSupplierOrderItem()) && (0 == $unit->getAdjustedQuantity()))
                        || ($unit->getSoldQuantity() < $unit->getOrderedQuantity() + $unit->getAdjustedQuantity())
                    );
            }
        };

        return $this->replaceAndFilter($fetched, $subject, $filter);
    }

    /**
     * @inheritDoc
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
                // - Not adjusted
                // - Not closed
                return is_null($unit->getSupplierOrderItem())
                    && 0 == $unit->getAdjustedQuantity()
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
     * @param array<StockUnitInterface>  $fetchedUnits
     */
    protected function replaceAndFilter(
        array $fetchedUnits,
        StockSubjectInterface $subject,
        FilterInterface $filter
    ): array {
        $filtered = [];

        $addedUnits   = $this->unitCache->findAddedBySubject($subject);
        $removedUnits = $this->unitCache->findRemovedBySubject($subject);

        // Loop through fetched units
        foreach ($fetchedUnits as $fetchedUnit) {
            // Compare with cache's added units
            foreach ($addedUnits as $index => $cachedUnit) {
                if ($cachedUnit->getId() === $fetchedUnit->getId()) {
                    // Use cached version
                    $filtered[] = $cachedUnit;
                    // Shift cached unit to not add twice
                    unset($addedUnits[$index]);

                    continue 2; // Found cached version, go to next fetched unit
                }
            }

            // Compare with cache's removed units
            foreach ($removedUnits as $cachedUnit) {
                if ($cachedUnit->getId() === $fetchedUnit->getId()) {
                    // Unit has been removed, got to next fetched unit
                    continue 2;
                }
            }

            // Cached unit version not found, use fetched one
            $filtered[] = $fetchedUnit;
        }

        // Add cache's remaining added units
        foreach ($addedUnits as $cachedUnit) {
            $filtered[] = $cachedUnit;
        }

        return array_filter($filtered, [$filter, 'filter']);
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array [StockSubjectInterface, StockUnitRepositoryInterface]
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
     */
    protected function getRepositoryBySubject(StockSubjectInterface $subject): StockUnitRepositoryInterface
    {
        // TODO use \Ekyna\Component\Commerce\Stock\Helper\StockUnitHelper::getRepository
        $class = $subject::getStockUnitClass();

        $repository = $this->repositoryFactory->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new UnexpectedTypeException($repository, StockUnitRepositoryInterface::class);
        }

        return $repository;
    }

    /**
     * Returns the subject and his stock unit repository.
     */
    protected function getFactoryBySubject(StockSubjectInterface $subject): ResourceFactoryInterface
    {
        $class = $subject::getStockUnitClass();

        return $this->factoryFactory->getFactory($class);
    }
}
