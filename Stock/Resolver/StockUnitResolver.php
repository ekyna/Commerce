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
     * @inheritdoc
     */
    public function getProviderByRelative(SubjectRelativeInterface $relative)
    {
        return $this
            ->subjectProviderRegistry
            ->getProviderByRelative($relative);
    }

    /**
     * Creates (and initializes) a stock unit for the given supplier order item.
     *
     * @param SupplierOrderItemInterface $item
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface
     */
    public function createBySupplierOrderItem(SupplierOrderItemInterface $item)
    {
        /** @var StockSubjectInterface $subject */
        $subject = $this->subjectHelper->resolve($item);

        $stockUnit = $this
            ->getRepositoryBySubject($subject)
            ->createNew();

        return $stockUnit
            ->setSubject($subject)
            ->setSupplierOrderItem($item)
            ->setNetPrice($item->getNetPrice())
            ->setOrderedQuantity($item->getQuantity())
            ->setEstimatedDateOfArrival($item->getOrder()->getEstimatedDateOfArrival());
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryByRelative(SubjectRelativeInterface $relative)
    {
        $subject = $this->subjectHelper->resolve($relative);

        if (!$subject instanceof StockSubjectInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockSubjectInterface::class);
        }

        return $this->getRepositoryBySubject($subject);
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryBySubject(StockSubjectInterface $subject)
    {
        // TODO repository cache map

        $repository = $this->entityManager->getRepository($subject::getStockUnitClass());

        if (!$repository instanceof StockUnitRepositoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . StockUnitRepositoryInterface::class);
        }

        return $repository;
    }
}
