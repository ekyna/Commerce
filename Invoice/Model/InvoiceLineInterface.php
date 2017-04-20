<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Model;

use Decimal\Decimal;
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

    public function getExpected(): ?Decimal;

    public function setExpected(?Decimal $expected): InvoiceLineInterface;

    public function getAvailable(): ?Decimal;

    public function setAvailable(?Decimal $available): InvoiceLineInterface;
}
