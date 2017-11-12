<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface SaleCopierInterface
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleCopierInterface
{
    /**
     * Copy the source sale into the target sale.
     *
     * @param Model\SaleInterface $source
     * @param Model\SaleInterface $target
     */
    public function copySale(Model\SaleInterface $source, Model\SaleInterface $target);

    /**
     * Copy the source adjustment into the target adjustment.
     *
     * @param Model\SaleAddressInterface $source
     * @param Model\SaleAddressInterface $target
     */
    public function copyAddress(Model\SaleAddressInterface $source, Model\SaleAddressInterface $target);

    /**
     * Copy the source adjustment into the target adjustment.
     *
     * @param Model\AdjustmentInterface $source
     * @param Model\AdjustmentInterface $target
     */
    public function copyAdjustment(Model\AdjustmentInterface $source, Model\AdjustmentInterface $target);

    /**
     * Copy the source payment into the target payment.
     *
     * @param PaymentInterface $source
     * @param PaymentInterface $target
     */
    public function copyPayment(PaymentInterface $source, PaymentInterface $target);

    /**
     * Copy the source item into the target item.
     *
     * @param Model\SaleItemInterface $source
     * @param Model\SaleItemInterface $target
     */
    public function copyItem(Model\SaleItemInterface $source, Model\SaleItemInterface $target);
}
