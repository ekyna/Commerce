<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractAssignment;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;

/**
 * Class ProductionItemAssignment
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductionItemAssignment extends AbstractAssignment
{
    private ?ProductionItemInterface $productionItem = null;

    public function getProductionItem(): ?ProductionItemInterface
    {
        return $this->productionItem;
    }

    public function setProductionItem(
        ?ProductionItemInterface $productionItem
    ): ProductionItemAssignment {
        if ($productionItem === $this->productionItem) {
            return $this;
        }

        if ($previous = $this->productionItem) {
            $this->productionItem = null;
            $previous->removeStockAssignment($this);
        }

        if ($this->productionItem = $productionItem) {
            $this->productionItem->addStockAssignment($this);
        }

        return $this;
    }

    public function getAssignable(): ?AssignableInterface
    {
        return $this->productionItem;
    }

    public function setAssignable(?AssignableInterface $assignable): AssignmentInterface
    {
        return $this->setProductionItem($assignable);
    }

    public function isRemovalPrevented(): bool
    {
        if (null === $order = $this->productionItem?->getProductionOrder()) {
            return false;
        }

        if (!POState::isStockableState($order->getState())) {
            return false;
        }

        return parent::isRemovalPrevented();
    }
}
