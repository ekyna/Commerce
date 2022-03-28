<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Document\Model\DocumentLineInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface InvoiceLineInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceLineInterface extends DocumentLineInterface, ResourceInterface
{
    public function getInvoice(): ?InvoiceInterface;

    public function setInvoice(?InvoiceInterface $invoice): InvoiceLineInterface;

    public function setChildren(array $children): InvoiceLineInterface;

    /**
     * @return Collection<InvoiceLineInterface>
     */
    public function getChildren(): Collection;

    public function clearChildren(): InvoiceLineInterface;

    public function getAvailability(): ?InvoiceAvailability;

    public function setAvailability(?InvoiceAvailability $availability): InvoiceLineInterface;

    public function isQuantityLocked(): bool;
}
