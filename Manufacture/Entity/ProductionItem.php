<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableTrait;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceTrait;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ProductionItem
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionItem extends AbstractResource implements ProductionItemInterface
{
    use SubjectReferenceTrait;
    use AssignableTrait;

    private ?ProductionOrderInterface $order      = null;
    private ?string                   $designation = null;
    private ?string                   $reference   = null;
    private Decimal                   $quantity;


    public function __construct()
    {
        $this->quantity = new Decimal(0);

        $this->initializeSubjectIdentity();
        $this->initializeAssignments();
    }

    public function __toString(): string
    {
        return $this->getDesignation() ?? 'New production item';
    }

    public function getProductionOrder(): ?ProductionOrderInterface
    {
        return $this->order;
    }

    public function setProductionOrder(?ProductionOrderInterface $order): ProductionItemInterface
    {
        $this->order = $order;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): ProductionItemInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): ProductionItemInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): ProductionItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotalQuantity(): Decimal
    {
        return $this->quantity->mul($this->order->getQuantity());
    }

    public function getAssignmentClass(): string
    {
        return ProductionItemAssignment::class;
    }
}
