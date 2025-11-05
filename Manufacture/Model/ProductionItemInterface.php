<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductionItemInterface
 * @package Ekyna\Component\Commerce\Manufacture\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionItemInterface extends ResourceInterface, AssignableInterface
{
    public function getProductionOrder(): ?ProductionOrderInterface;

    public function setProductionOrder(?ProductionOrderInterface $order): ProductionItemInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): ProductionItemInterface;

    public function getReference(): ?string;

    public function setReference(?string $reference): ProductionItemInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): ProductionItemInterface;

    public function getTotalQuantity(): Decimal;
}
