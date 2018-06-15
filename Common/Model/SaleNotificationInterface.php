<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleNotificationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleNotificationInterface extends NotificationInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface|null
     */
    public function getSale();

    /**
     * Sets the sale.
     *
     * @param SaleInterface $sale
     *
     * @return $this|SaleNotificationInterface
     */
    public function setSale(SaleInterface $sale = null);
}
