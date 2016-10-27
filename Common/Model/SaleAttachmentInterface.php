<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleAttachmentInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleAttachmentInterface extends AttachmentInterface
{
    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale();

    /**
     * Sets the sale.
     *
     * @param SaleInterface|null $sale
     *
     * @return $this|SaleAttachmentInterface
     */
    public function setSale(SaleInterface $sale = null);
}
