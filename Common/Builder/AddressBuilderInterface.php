<?php

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
     * @param Model\SaleInterface    $sale
     * @param Model\AddressInterface $source
     * @param bool                   $persistence
     *
     * @return bool Whether the sale's delivery address has been changed or not.
     */
    public function buildSaleInvoiceAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        $persistence = false
    );

    /**
     * Builds the sale delivery address from the given address.
     *
     * @param Model\SaleInterface    $sale
     * @param Model\AddressInterface $source
     * @param bool                   $persistence
     *
     * @return bool Whether the sale's delivery address has been changed or not.
     */
    public function buildSaleDeliveryAddressFromAddress(
        Model\SaleInterface $sale,
        Model\AddressInterface $source,
        $persistence = false
    );
}
