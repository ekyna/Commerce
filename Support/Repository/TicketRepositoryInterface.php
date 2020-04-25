<?php

namespace Ekyna\Component\Commerce\Support\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface TicketRepositoryInterface
 * @package Ekyna\Component\Commerce\Support\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the oldest opened tickets.
     *
     * @param int $limit
     *
     * @return TicketInterface[]
     */
    public function findOpened(int $limit = 10): array;

    /**
     * Finds the oldest not closed tickets.
     *
     * @param int $limit
     *
     * @return TicketInterface[]
     */
    public function findNotClosed(int $limit = 10): array;

    /**
     * Finds tickets by customer.
     *
     * @param CustomerInterface $customer
     * @param bool              $admin
     *
     * @return TicketInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, bool $admin): array;

    /**
     * Finds tickets by order.
     *
     * @param OrderInterface $order
     * @param bool           $admin
     *
     * @return TicketInterface[]
     */
    public function findByOrder(OrderInterface $order, bool $admin): array;

    /**
     * Finds tickets by quote.
     *
     * @param QuoteInterface $quote
     * @param bool           $admin
     *
     * @return TicketInterface[]
     */
    public function findByQuote(QuoteInterface $quote, bool $admin): array;
}
