<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Class StockComponent
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockComponent
{
    private StockSubjectInterface $subject;
    private Decimal               $quantity;

    private ?Decimal $inStock        = null;
    private ?Decimal $availableStock = null;
    private ?Decimal $virtualStock   = null;

    public function __construct(StockSubjectInterface $subject, Decimal $quantity)
    {
        $this->subject = $subject;
        $this->quantity = $quantity;
    }

    public function getSubject(): StockSubjectInterface
    {
        return $this->subject;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function getStockMode(): string
    {
        return $this->subject->getStockMode();
    }

    public function getStockState(): string
    {
        return $this->subject->getStockState();
    }

    public function getInStock(): Decimal
    {
        if (null !== $this->inStock) {
            return $this->inStock;
        }

        return $this->inStock = $this->subject->getInStock()->div($this->quantity);
    }

    public function getAvailableStock(): Decimal
    {
        if (null !== $this->availableStock) {
            return $this->availableStock;
        }

        return $this->availableStock = $this->subject->getAvailableStock()->div($this->quantity);
    }

    public function getVirtualStock(): Decimal
    {
        if (null !== $this->virtualStock) {
            return $this->virtualStock;
        }

        return $this->virtualStock = $this->subject->getVirtualStock()->div($this->quantity);
    }

    public function getEstimatedDateOfArrival(): ?DateTimeInterface
    {
        return $this->subject->getEstimatedDateOfArrival();
    }
}
