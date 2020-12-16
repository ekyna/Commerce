<?php

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var float
     */
    protected $expected;

    /**
     * @var float
     */
    protected $available;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->clearChildren();
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
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
        if ($document && !$document instanceof Model\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Model\InvoiceInterface::class);
        }

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
            if ($previous = $this->invoice) {
                $this->invoice = null;
                $previous->removeLine($this);
            }

            if ($this->invoice = $invoice) {
                $this->invoice->addLine($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setChildren(array $children)
    {
        $this->clearChildren();

        foreach ($children as $child) {
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function clearChildren()
    {
        $this->children = new ArrayCollection();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @inheritdoc
     */
    public function setExpected($expected)
    {
        $this->expected = (float)$expected;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @inheritdoc
     */
    public function setAvailable($available)
    {
        $this->available = (float)$available;

        return $this;
    }
}
