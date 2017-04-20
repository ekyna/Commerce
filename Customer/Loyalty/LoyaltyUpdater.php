<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Loyalty;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class LoyaltyUpdater
 * @package Ekyna\Component\Commerce\Customer\Loyalty
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyUpdater
{
    private PersistenceHelperInterface $persistenceHelper;
    private LoyaltyLogger $logger;

    public function __construct(PersistenceHelperInterface $persistenceHelper, LoyaltyLogger $logger)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->logger = $logger;
    }

    /**
     * Adds loyalty points to the given customer.
     *
     * @param CustomerInterface $customer The customer
     * @param int               $points   The amount of points to add
     * @param string            $origin   The reason string (must be unique).
     *
     * Origin is used as a key to prevent duplicate additions.
     * Example origins: "Birthday 2020", "Newsletter subscribed", "Order #123 completed"
     */
    public function add(CustomerInterface $customer, int $points, string $origin): void
    {
        if ($this->logger->has($customer, $origin)) {
            return;
        }

        $customer->setLoyaltyPoints($customer->getLoyaltyPoints() + $points);
        $this->persistenceHelper->persistAndRecompute($customer, false);

        $this->logger->add($customer, $points, false, $origin);
    }

    /**
     * Removes loyalty points from the given customer.
     *
     * @param CustomerInterface $customer The customer
     * @param int               $points   The amount of points to remove
     * @param string            $origin   The reason string (must be unique).
     *
     * Origin is used as a key to prevent duplicate removals.
     * Example origins: "Newsletter unsubscribed", "Order #123 refunded"
     */
    public function remove(CustomerInterface $customer, int $points, string $origin): void
    {
        if ($this->logger->has($customer, $origin)) {
            return;
        }

        $results = $customer->getLoyaltyPoints() - $points;
        if (0 > $results) {
            $results = 0;
        }

        $customer->setLoyaltyPoints($results);
        $this->persistenceHelper->persistAndRecompute($customer, false);

        $this->logger->add($customer, $points, true, $origin);
    }
}
