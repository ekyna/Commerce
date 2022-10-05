<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Loyalty;

use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(
        private readonly LoyaltyLogRepositoryInterface $repository,
        private readonly PersistenceHelperInterface    $persistenceHelper,
        private readonly EntityManagerInterface        $entityManager
    ) {
    }

    /**
     * Returns whether a log exists for the given customer and origin.
     */
    public function has(CustomerInterface $customer, string $origin): bool
    {
        return null !== $this->repository->findByCustomerAndOrigin($customer, $origin);
    }

    /**
     * Logs customer's loyalty points update.
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

        // TODO $this->persistenceHelper->persistAndRecompute($log);

        $this->entityManager->persist($log);

        if (!$this->persistenceHelper->getEventQueue()->isOpened()) {
            return;
        }

        $uow = $this->entityManager->getUnitOfWork();
        $metadata = $this->entityManager->getClassMetadata(LoyaltyLog::class);
        $uow->computeChangeSet($metadata, $log);
    }
}
