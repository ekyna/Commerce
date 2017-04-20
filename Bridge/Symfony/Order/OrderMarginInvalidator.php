<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Order;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Invalidator\OrderMarginInvalidator as BaseInvalidator;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OrderMarginInvalidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderMarginInvalidator extends BaseInvalidator implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private string $assignmentClass;
    private string $orderClass;


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
        if (empty($this->unitIds)) {
            return;
        }

        $qb = $this->entityManager->createQueryBuilder();
        $ex = $qb->expr();

        $query = $qb
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
            ->getQuery();

        try {
            $result = $query
                ->setParameter('unitIds', $this->unitIds)
                ->getScalarResult();
        } catch (Exception $e) {
            // Fail silently if connection failed or table is not found.
            if ($e instanceof ConnectionException || $e instanceof TableNotFoundException) {
                return;
            }

            throw $e;
        }

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

    public static function getSubscribedEvents(): array
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
