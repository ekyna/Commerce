<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractInvoiceItem
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceItem extends Document\DocumentItem implements Invoice\InvoiceItemInterface
{
    use SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Invoice\InvoiceInterface
     */
    protected $invoice;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     *
     * @return Invoice\InvoiceInterface
     */
    public function getDocument(): ?Document\DocumentInterface
    {
        return $this->getInvoice();
    }

    /**
     * @inheritdoc
     */
    public function setDocument(Document\DocumentInterface $document = null): Document\DocumentItemInterface
    {
        if ($document && !$document instanceof Invoice\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Invoice\InvoiceInterface::class);
        }

        return $this->setInvoice($document);
    }

    /**
     * @inheritdoc
     */
    public function getInvoice(): ?Invoice\InvoiceInterface
    {
        return $this->invoice;
    }

    /**
     * @inheritdoc
     */
    public function setInvoice(Invoice\InvoiceInterface $invoice = null): Invoice\InvoiceItemInterface
    {
        if ($this->invoice !== $invoice) {
            if ($previous = $this->invoice) {
                $this->invoice = null;
                $previous->removeItem($this);
            }

            if ($this->invoice = $invoice) {
                $this->invoice->addItem($this);
            }
        }

        return $this;
    }
}
