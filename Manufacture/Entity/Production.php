<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

use function sprintf;

/**
 * Class Production
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Production extends AbstractResource implements ProductionInterface
{
    use TimestampableTrait;

    private ?ProductionOrderInterface $productionOrder = null;
    private ?int                      $number          = null;
    private ?int                      $quantity        = null;

    public function __construct()
    {
        $this->initializeTimestampable();
    }

    public function __toString(): string
    {
        return sprintf('%s-%d', $this->productionOrder ?: 'NewPO', $this->number ?: '#');
    }

    public function getProductionOrder(): ?ProductionOrderInterface
    {
        return $this->productionOrder;
    }

    public function setProductionOrder(?ProductionOrderInterface $order): ProductionInterface
    {
        if ($order === $this->productionOrder) {
            return $this;
        }

        if ($previous = $this->productionOrder) {
            $this->productionOrder = null;
            $previous->removeProduction($this);
        }

        if ($this->productionOrder = $order) {
            $this->productionOrder->addProduction($this);
        }

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): Production
    {
        $this->number = $number;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): ProductionInterface
    {
        $this->quantity = $quantity;

        return $this;
    }
}
