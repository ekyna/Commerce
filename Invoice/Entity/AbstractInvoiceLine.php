<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Class AbstractInvoiceLine
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLine extends Document\DocumentLine implements Model\InvoiceLineInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\InvoiceInterface
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
     * @return Model\InvoiceInterface
     */
    public function getDocument()
    {
        return $this->getInvoice();
    }

    /**
     * @inheritdoc
     */
    public function setDocument(Document\DocumentInterface $document = null)
    {
        return $this->setInvoice($document);
    }

    /**
     * @inheritdoc
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @inheritdoc
     */
    public function setInvoice(Model\InvoiceInterface $invoice = null)
    {
        if ($this->invoice !== $invoice) {
            $previous = $this->invoice;
            $this->invoice = $invoice;

            if ($previous) {
                $previous->removeLine($this);
            }

            if ($this->invoice) {
                $this->invoice->addLine($this);
            }
        }

        return $this;
    }
}
