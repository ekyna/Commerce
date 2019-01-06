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
     *
     * @return TicketInterface[]
     */
    public function findByCustomer(CustomerInterface $customer);

    /**
     * Finds tickets by order.
     *
     * @param OrderInterface $order
     *
     * @return TicketInterface[]
     */
    public function findByOrder(OrderInterface $order);

    /**
     * Finds tickets by quote.
     *
     * @param QuoteInterface $quote
     *
     * @return TicketInterface[]
     */
    public function findByQuote(QuoteInterface $quote);
}
