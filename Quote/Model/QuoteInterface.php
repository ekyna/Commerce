<?php

namespace Ekyna\Component\Commerce\Quote\Model;

use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface QuoteInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteInterface extends Common\SaleInterface
{
    /**
     * Returns whether this quote can be modified by the customer.
     *
     * @return bool
     */
    public function isEditable(): bool;

    /**
     * Sets whether this quote can be modified by the customer.
     *
     * @param bool $editable
     *
     * @return QuoteInterface
     */
    public function setEditable(bool $editable): QuoteInterface;

    /**
     * Returns the "expires at" date time.
     *
     * @return \DateTime
     */
    public function getExpiresAt();

    /**
     * Sets the "expires at" date time.
     *
     * @param \DateTime $expiresAt
     *
     * @return $this|QuoteInterface
     */
    public function setExpiresAt(\DateTime $expiresAt = null);

    /**
     * Returns whether or not the quote is expired.
     *
     * @return bool
     */
    public function isExpired();

    /**
     * Returns whether this quote requires a voucher.
     *
     * @return bool
     */
    public function requiresVoucher(): bool;

    /**
     * Returns whether this quote has voucher number and attachment set.
     *
     * @return bool
     */
    public function hasVoucher(): bool;

    /**
     * Returns the voucher attachment if set.
     *
     * @return Common\SaleAttachmentInterface|null
     */
    public function getVoucherAttachment();
}
