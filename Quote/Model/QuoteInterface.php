<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\Model;

use DateTimeInterface;
use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface QuoteInterface
 * @package Ekyna\Component\Commerce\Quote\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteInterface extends Common\SaleInterface, Common\InitiatorSubjectInterface
{
    /**
     * Returns whether this quote can be modified by the customer.
     */
    public function isEditable(): bool;

    /**
     * Sets whether this quote can be modified by the customer.
     */
    public function setEditable(bool $editable): QuoteInterface;

    public function getExpiresAt(): ?DateTimeInterface;

    public function setExpiresAt(?DateTimeInterface $expiresAt): QuoteInterface;

    public function isExpired(): bool;

    public function requiresVoucher(): bool;

    public function hasVoucher(): bool;

    public function getVoucherAttachment(): ?QuoteAttachmentInterface;
}
