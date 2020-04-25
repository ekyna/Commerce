<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Order;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Invalidator\OrderMarginInvalidator as BaseInvalidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OrderMarginInvalidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderMarginInvalidator extends BaseInvalidator implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $assignmentClass;

    /**
     * @var string
     */
    private $orderClass;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $assignmentClass
     * @param string                 $orderClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $assignmentClass,
        string $orderClass
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->assignmentClass = $assignmentClass;
        $this->orderClass = $orderClass;
    }

    /**
     * Invalidates orders margin total by stock unit.
     */
    public function invalidate(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $ex = $qb->expr();

        $result = $qb
            ->select(<<<EOL
            IFNULL(
                IDENTITY(i1.order),
                IFNULL(
                    IDENTITY(i2.order),
                    IFNULL(
                        IDENTITY(i3.order),
                        IFNULL(
                            IDENTITY(i4.order),
                            IFNULL(
                                IDENTITY(i5.order),
                                IDENTITY(i6.order)
                            )
                        )
                    )
                )
            ) as order_id
            EOL
            )
            ->from($this->assignmentClass, 'a')
            ->join('a.orderItem', 'i1')
            ->leftJoin('i1.parent', 'i2')
            ->leftJoin('i2.parent', 'i3')
            ->leftJoin('i3.parent', 'i4')
            ->leftJoin('i4.parent', 'i5')
            ->leftJoin('i5.parent', 'i6')
            ->where($ex->in('IDENTITY(a.stockUnit)', ':unitIds'))
            ->getQuery()
            ->setParameter('unitIds', $this->unitIds)
            ->getScalarResult();

        if (empty($orderIds = array_column($result, 'order_id'))) {
            return;
        }

        $this
            ->entityManager
            ->createQueryBuilder()
            ->update($this->orderClass, 'o')
            ->set('o.marginTotal', ':value')
            ->set('o.revenueTotal', ':value')
            ->where($ex->in('o.id', ':order_ids'))
            ->getQuery()
            ->setParameters([
                'order_ids' => $orderIds,
                'value'     => null,
            ])
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        $listeners = [
            KernelEvents::TERMINATE => ['invalidate', 1024], // Before Symfony EmailSenderListener
        ];

        if (class_exists('Symfony\Component\Console\ConsoleEvents')) {
            $listeners[constant('Symfony\Component\Console\ConsoleEvents::TERMINATE')] = [
                'invalidate',
                1024,
            ]; // Before Symfony EmailSenderListener
        }

        return $listeners;
    }
}
