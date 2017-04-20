<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    protected ?int                    $id      = null;
    protected ?Model\InvoiceInterface $invoice = null;
    protected Collection              $children;

    /* Non-mapped fields */
    protected ?Decimal $expected  = null;
    protected ?Decimal $available = null;

    public function __construct()
    {
        parent::__construct();

        $this->clearChildren();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Model\InvoiceInterface
     */
    public function getDocument(): ?Document\DocumentInterface
    {
        return $this->getInvoice();
    }

    public function setDocument(?Document\DocumentInterface $document): Document\DocumentLineInterface
    {
        if ($document && !$document instanceof Model\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Model\InvoiceInterface::class);
        }

        return $this->setInvoice($document);
    }

    public function getInvoice(): ?Model\InvoiceInterface
    {
        return $this->invoice;
    }

    public function setInvoice(?Model\InvoiceInterface $invoice): Model\InvoiceLineInterface
    {
        if ($this->invoice === $invoice) {
            return $this;
        }

        if ($previous = $this->invoice) {
            $this->invoice = null;
            $previous->removeLine($this);
        }

        if ($this->invoice = $invoice) {
            $this->invoice->addLine($this);
        }

        return $this;
    }

    public function setChildren(array $children): Model\InvoiceLineInterface
    {
        $this->clearChildren();

        foreach ($children as $child) {
            $this->children->add($child);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function clearChildren(): Model\InvoiceLineInterface
    {
        $this->children = new ArrayCollection();

        return $this;
    }

    public function getExpected(): ?Decimal
    {
        return $this->expected;
    }

    public function setExpected(?Decimal $expected): Model\InvoiceLineInterface
    {
        $this->expected = $expected;

        return $this;
    }

    public function getAvailable(): ?Decimal
    {
        return $this->available;
    }

    public function setAvailable(?Decimal $available): Model\InvoiceLineInterface
    {
        $this->available = $available;

        return $this;
    }
}
