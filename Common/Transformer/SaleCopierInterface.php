<?php

declare(strict_types=1);

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
     */
    public function copySale(): SaleCopierInterface;

    /**
     * Copies the source sale's data into the target sale.
     */
    public function copyData(): SaleCopierInterface;

    /**
     * Copies the source sale's addresses into the target sale.
     */
    public function copyAddresses(): SaleCopierInterface;

    /**
     * Copies the source sale's attachments into the target sale.
     */
    public function copyAttachments(): SaleCopierInterface;

    /**
     * Copies the source sale's notifications into the target sale.
     */
    public function copyNotifications(): SaleCopierInterface;

    /**
     * Copies the source sale's adjustments into the target sale.
     */
    public function copyAdjustments(): SaleCopierInterface;

    /**
     * Copies the source sale's items into the target sale.
     */
    public function copyItems(): SaleCopierInterface;

    /**
     * Copies the source sale's payments into the target sale.
     */
    public function copyPayments(): SaleCopierInterface;
}
