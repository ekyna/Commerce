<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Order;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Invalidator\OrderMarginInvalidator as BaseInvalidator;
use Ekyna\Component\Commerce\Order\Message\UpdateOrderMargin;
use Ekyna\Component\Resource\Message\MessageQueueInterface;
use Exception;

/**
 * Class OrderMarginInvalidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderMarginInvalidator extends BaseInvalidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageQueueInterface  $messageQueue,
        private readonly string                 $assignmentClass
    ) {
        parent::__construct();
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

        foreach ($orderIds as $orderId) {
            $this->messageQueue->addMessage(new UpdateOrderMargin((int)$orderId));
        }
    }
}
