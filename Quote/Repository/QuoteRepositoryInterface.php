<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Repository;

use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Interface QuoteRepositoryInterface
 * @package Ekyna\Component\Commerce\Quote\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements SaleRepositoryInterface<QuoteInterface>
 */
interface QuoteRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Finds quotes initiated by the given customer or its children.
     *
     * @return array<QuoteInterface>
     */
    public function findByInitiatorCustomer(CustomerInterface $initiator): array;

    /**
     * Finds quotes having obsolete project.
     *
     * @return array<QuoteInterface>
     */
    public function findObsoleteProjects(): array;
}
