<?php

namespace Ekyna\Component\Commerce\Customer\Loyalty;

use Ekyna\Component\Commerce\Customer\Entity\LoyaltyLog;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\LoyaltyLogRepositoryInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class LoyaltyLogger
 * @package Ekyna\Component\Commerce\Customer\Loyalty
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyLogger
{
    /**
     * @var LoyaltyLogRepositoryInterface
     */
    private $repository;

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;


    /**
     * Constructor.
     *
     * @param LoyaltyLogRepositoryInterface $repository
     * @param PersistenceHelperInterface    $persistenceHelper
     */
    public function __construct(LoyaltyLogRepositoryInterface $repository, PersistenceHelperInterface $persistenceHelper)
    {
        $this->repository = $repository;
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Returns whether a log exists for the given customer and origin.
     *
     * @param CustomerInterface $customer
     * @param string            $origin
     *
     * @return bool
     */
    public function has(CustomerInterface $customer, string $origin): bool
    {
        return null !== $this->repository->findByCustomerAndOrigin($customer, $origin);
    }

    /**
     * Logs customer's loyalty points update.
     *
     * @param CustomerInterface $customer
     * @param int               $points
     * @param bool              $debit
     * @param string            $origin
     */
    public function add(CustomerInterface $customer, int $points, bool $debit, string $origin): void
    {
        if ($this->has($customer, $origin)) {
            return;
        }

        $log = new LoyaltyLog();
        $log
            ->setCustomer($customer)
            ->setDebit($debit)
            ->setAmount($points)
            ->setOrigin($origin);

        $manager = $this->persistenceHelper->getManager();
        $manager->persist($log);

        if ($this->persistenceHelper->getEventQueue()->isOpened()) {
            $uow = $manager->getUnitOfWork();
            $metadata = $manager->getClassMetadata(LoyaltyLog::class);
            $uow->computeChangeSet($metadata, $log);
        }
    }
}
