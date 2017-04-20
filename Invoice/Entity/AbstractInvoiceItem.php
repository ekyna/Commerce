<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractInvoiceItem
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceItem extends Document\DocumentItem implements Invoice\InvoiceItemInterface
{
    use SortableTrait;

    protected ?int                      $id = null;
    protected ?Invoice\InvoiceInterface $invoice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Document\DocumentInterface
    {
        return $this->getInvoice();
    }

    public function setDocument(?Document\DocumentInterface $document): Document\DocumentItemInterface
    {
        if ($document && !$document instanceof Invoice\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Invoice\InvoiceInterface::class);
        }

        return $this->setInvoice($document);
    }

    public function getInvoice(): ?Invoice\InvoiceInterface
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice\InvoiceInterface $invoice): Invoice\InvoiceItemInterface
    {
        if ($this->invoice === $invoice) {
            return $this;
        }

        if ($previous = $this->invoice) {
            $this->invoice = null;
            $previous->removeItem($this);
        }

        if ($this->invoice = $invoice) {
            $this->invoice->addItem($this);
        }

        return $this;
    }
}
