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
     * Returns whether or not the quote has voucher number and attachment set.
     *
     * @return bool
     */
    public function hasVoucher();

    /**
     * Returns the voucher attachment if set.
     *
     * @return Common\SaleAttachmentInterface|null
     */
    public function getVoucherAttachment();
}
