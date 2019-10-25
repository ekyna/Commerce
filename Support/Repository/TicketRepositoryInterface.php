<?php

namespace Ekyna\Component\Commerce\Support\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;

/**
 * Interface TicketRepositoryInterface
 * @package Ekyna\Component\Commerce\Support\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketRepositoryInterface
{
    /**
     * Finds the oldest opened tickets.
     *
     * @param int $limit
     *
     * @return TicketInterface[]
     */
    public function findOpened(int $limit = 4);

    /**
     * Finds tickets by customer.
     *
     * @param CustomerInterface $customer
     * @param bool              $admin
     *
     * @return TicketInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, bool $admin);

    /**
     * Finds tickets by order.
     *
     * @param OrderInterface $order
     * @param bool           $admin
     *
     * @return TicketInterface[]
     */
    public function findByOrder(OrderInterface $order, bool $admin);

    /**
     * Finds tickets by quote.
     *
     * @param QuoteInterface $quote
     * @param bool           $admin
     *
     * @return TicketInterface[]
     */
    public function findByQuote(QuoteInterface $quote, bool $admin);
}
