<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface TicketRepositoryInterface
 * @package Ekyna\Component\Commerce\Support\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<TicketRepositoryInterface>
 */
interface TicketRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the oldest opened tickets.
     *
     * @return array<TicketInterface>
     */
    public function findOpened(int $limit = 10): array;

    /**
     * Finds the oldest not closed tickets.
     *
     * @return array<TicketInterface>
     */
    public function findNotClosed(int $limit = 10): array;

    /**
     * Finds tickets by customer.
     *
     * @return array<TicketInterface>
     */
    public function findByCustomer(CustomerInterface $customer, bool $admin): array;

    /**
     * Finds tickets by order.
     *
     * @return array<TicketInterface>
     */
    public function findByOrder(OrderInterface $order, bool $admin): array;

    /**
     * Finds tickets by quote.
     *
     * @return array<TicketInterface>
     */
    public function findByQuote(QuoteInterface $quote, bool $admin): array;
}
