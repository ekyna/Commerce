<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Resource\Copier\CopyInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface BillOfMaterialsInterface
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BillOfMaterialsInterface
    extends ResourceInterface,
            NumberSubjectInterface,
            SubjectReferenceInterface,
            TimestampableInterface,
            CopyInterface
{
    public function getVersion(): int;

    public function setVersion(int $version): BillOfMaterialsInterface;

    public function getState(): BOMState;

    public function setState(BOMState $state): BillOfMaterialsInterface;

    /**
     * @return Collection<BOMComponentInterface>
     */
    public function getComponents(): Collection;

    public function hasComponent(BOMComponentInterface $component): bool;

    public function addComponent(BOMComponentInterface $component): BillOfMaterialsInterface;

    public function removeComponent(BOMComponentInterface $component): BillOfMaterialsInterface;

    /**
     * @param Collection<BOMComponentInterface> $components
     */
    public function setComponents(Collection $components): BillOfMaterialsInterface;
}
