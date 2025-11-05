<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Class ProductionInterface
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionInterface extends ResourceInterface, TimestampableInterface
{
    public function getProductionOrder(): ?ProductionOrderInterface;

    public function setProductionOrder(?ProductionOrderInterface $order): ProductionInterface;

    public function getQuantity(): ?int;

    public function setQuantity(?int $quantity): ProductionInterface;
}
