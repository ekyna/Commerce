<?php

namespace Ekyna\Component\Commerce\Common\Transformer;

/**
 * Interface SaleCopierInterface
 * @package Ekyna\Component\Commerce\Common\Transformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleCopierInterface
{
    /**
     * Copies the whole source sale into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copySale();

    /**
     * Copies the source sale's data into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyData();

    /**
     * Copies the source sale's addresses into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyAddresses();

    /**
     * Copies the source sale's attachments into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyAttachments();

    /**
     * Copies the source sale's notifications into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyNotifications();

    /**
     * Copies the source sale's adjustments into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyAdjustments();

    /**
     * Copies the source sale's items into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyItems();

    /**
     * Copies the source sale's payments into the target sale.
     *
     * @return SaleCopierInterface
     */
    public function copyPayments();

    /**
     * Copies the source sale's shipments into the target sale.
     *
     * @return SaleCopierInterface
     */
    //public function copyShipments();

    /**
     * Copies the source sale's invoices into the target sale.
     *
     * @return SaleCopierInterface
     */
    //public function copyInvoices();
}
