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
    public function getProject(): ?Common\ProjectInterface;

    public function setProject(?Common\ProjectInterface $project): QuoteInterface;

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

    public function getProjectDate(): ?DateTimeInterface;

    public function setProjectDate(?DateTimeInterface $date): QuoteInterface;

    public function getProjectTrust(): ?int;

    public function setProjectTrust(?int $trust): QuoteInterface;

    public function getProjectAlive(): ?bool;

    public function setProjectAlive(?bool $projectAlive): QuoteInterface;

    public function requiresVoucher(): bool;

    public function hasVoucher(): bool;

    public function getVoucherAttachment(): ?QuoteAttachmentInterface;
}
