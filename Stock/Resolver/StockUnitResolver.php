<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;

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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array (class => $repository)
     */
    protected $repositoryCache;

    /**
     * @var array (identity => $stockUnit)
     */
    protected $newStockUnitCache;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * Clears the cache.
     */
    public function clear()
    {
        $this->repositoryCache = [];
        $this->newStockUnitCache = [];
    }

    /**
     * @inheritdoc
     */
    public function createBySubjectRelative(SubjectRelativeInterface $relative)
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($relative);

        $stockUnit = $this
            ->getRepositoryBySubject($subject)
            ->createNew();

        $stockUnit->setSubject($subject);

        $oid = spl_object_hash($subject);

        if (!isset($this->newStockUnitCache[$oid])) {
            $this->newStockUnitCache[$oid] = [];
        }

        $this->newStockUnitCache[$oid][] = $stockUnit;

        return $stockUnit;
    }

    /**
     * @inheritdoc
     */
    public function createBySupplierOrderItem(SupplierOrderItemInterface $item)
    {
        return $this
            ->createBySubjectRelative($item)
            ->setSupplierOrderItem($item)
            ->setNetPrice($item->getNetPrice())
            ->setOrderedQuantity($item->getQuantity())
            ->setEstimatedDateOfArrival($item->getOrder()->getEstimatedDateOfArrival());
    }

    /**
     * @inheritdoc
     */
    public function findPending($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $repository->findPendingBySubject($subject);
    }

    /**
     * @inheritdoc
     */
    public function findPendingOrReady($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return $repository->findPendingOrReadyBySubject($subject);
    }

    /**
     * @inheritdoc
     */
    public function findNotClosed($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return array_merge(
            $repository->findNotClosedBySubject($subject),
            $this->getCachedStockUnits($subject)
        );
    }

    /**
     * @inheritdoc
     */
    public function findAssignable($subjectOrRelative)
    {
        /**
         * @var StockSubjectInterface        $subject
         * @var StockUnitRepositoryInterface $repository
         */
        list($subject, $repository) = $this->getSubjectAndRepository($subjectOrRelative);

        return array_merge(
            $repository->findAssignableBySubject($subject),
            $this->getCachedStockUnits($subject)
        );
    }

    /**
     * Returns the cached new stock units by subject.
     *
     * @param StockSubjectInterface $subject
     *
     * @return array|mixed
     */
    protected function getCachedStockUnits(StockSubjectInterface $subject)
    {
        $oid = spl_object_hash($subject);

        if (isset($this->newStockUnitCache[$oid])) {
            return $this->newStockUnitCache[$oid];
        }

        return [];
    }

    /**
     * Returns the subject and his stock unit repository.
     *
     * @param StockSubjectInterface|SubjectRelativeInterface $subjectOrRelative
     *
     * @return array(StockSubjectInterface, StockUnitRepositoryInterface)
     */
    protected function getSubjectAndRepository($subjectOrRelative)
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
     * @inheritdoc
     */
    protected function getRepositoryBySubject(StockSubjectInterface $subject)
    {
        $class = $subject::getStockUnitClass();

        if (isset($this->repositoryCache[$class])) {
            return $this->repositoryCache[$class];
        }

        $repository = $this->entityManager->getRepository($class);

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockUnitRepositoryInterface::class);
        }

        return $this->repositoryCache[$class] = $repository;
    }
}
