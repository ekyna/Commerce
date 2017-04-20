<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model;

/**
 * Interface AddressBuilderInterface
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressBuilderInterface
{
    /**
     * Builds the sale invoice address from the given address.
     *
     * @return bool Whether the sale's delivery address has been changed or not.
     */
    public function buildSaleInvoiceAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        bool $persistence = false
    ): bool;

    /**
     * Builds the sale delivery address from the given address.
     *
     * @return bool Whether the sale's delivery address has been changed or not.
     */
    public function buildSaleDeliveryAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        bool $persistence = false
    ): bool;
}
