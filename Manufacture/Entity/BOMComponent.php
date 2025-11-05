<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMComponentInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceTrait;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BOMComponent
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BOMComponent extends AbstractResource implements BOMComponentInterface
{
    use SubjectReferenceTrait;
    use SortableTrait;

    private ?BillOfMaterials $bom = null;
    private Decimal          $quantity;


    public function __construct()
    {
        $this->quantity = new Decimal(1);

        $this->initializeSubjectIdentity();
    }

    public function __toString(): string
    {
        return $this->bom . '_' . $this->position;
    }

    public function getBom(): ?BillOfMaterialsInterface
    {
        return $this->bom;
    }

    public function setBom(?BillOfMaterialsInterface $bom): BOMComponentInterface
    {
        if ($bom === $this->bom) {
            return $this;
        }

        if ($previous = $this->bom) {
            $this->bom = null;
            $previous->removeComponent($this);
        }

        if ($this->bom = $bom) {
            $this->bom->addComponent($this);
        }

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): BOMComponentInterface
    {
        $this->quantity = $quantity;

        return $this;
    }
}
