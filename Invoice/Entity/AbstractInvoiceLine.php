<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Resource\Model\ResourceTrait;

/**
 * Class AbstractInvoiceLine
 * @package Ekyna\Component\Commerce\Invoice\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLine extends Document\DocumentLine implements Model\InvoiceLineInterface
{
    use ResourceTrait;

    protected ?Model\InvoiceInterface $invoice = null;
    protected Collection              $children;

    /* Non-mapped fields */
    protected ?Model\InvoiceAvailability $availability = null;

    public function __construct()
    {
        parent::__construct();

        $this->clearChildren();
    }

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

    public function getAvailability(): ?Model\InvoiceAvailability
    {
        return $this->availability;
    }

    public function setAvailability(?Model\InvoiceAvailability $availability): Model\InvoiceLineInterface
    {
        $this->availability = $availability;

        return $this;
    }

    public function isQuantityLocked(): bool
    {
        if (null === $item = $this->getSaleItem()) {
            return false;
        }

        /* TODO if (null === $parent = $item->getParent()) {
            return false;
        }

        return $parent->isPrivate() || ($parent->isCompound() && $parent->hasPrivateChildren());*/

        return $item->isPrivate();
    }
}
