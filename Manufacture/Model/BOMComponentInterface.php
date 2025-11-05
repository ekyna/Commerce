<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BOMComponentInterface
 * @package Ekyna\Component\Commerce\Manufacture\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BOMComponentInterface extends SubjectReferenceInterface, SortableInterface, ResourceInterface
{
    public function getBom(): ?BillOfMaterialsInterface;

    public function setBom(?BillOfMaterialsInterface $bom): BOMComponentInterface;

    public function getQuantity(): Decimal;

    public function setQuantity(Decimal $quantity): BOMComponentInterface;
}
