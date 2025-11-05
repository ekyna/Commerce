<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectTrait;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMComponentInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceTrait;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

use function sprintf;

/**
 * Class BillOfMaterials
 * @package Ekyna\Component\Commerce\Manufacture\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterials extends AbstractResource implements BillOfMaterialsInterface
{
    use NumberSubjectTrait;
    use SubjectReferenceTrait;
    use TimestampableTrait;

    private int        $version;
    private BOMState   $state;
    /** @var Collection<BOMComponentInterface> */
    private Collection $components;


    public function __construct()
    {
        $this->version = 1;
        $this->state = BOMState::DRAFT;
        $this->components = new ArrayCollection();

        $this->initializeSubjectIdentity();
        $this->initializeTimestampable();
    }

    public function __toString(): string
    {
        return sprintf('%s-v%d', $this->number, $this->version);
    }

    public function onCopy(CopierInterface $copier): void
    {
        $copier->copyCollection($this, 'components', true);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): BillOfMaterialsInterface
    {
        $this->version = $version;

        return $this;
    }

    public function getState(): BOMState
    {
        return $this->state;
    }

    public function setState(BOMState $state): BillOfMaterialsInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<BOMComponentInterface>
     */
    public function getComponents(): Collection
    {
        return $this->components;
    }

    public function hasComponent(BOMComponentInterface $component): bool
    {
        return $this->components->contains($component);
    }

    public function addComponent(BOMComponentInterface $component): BillOfMaterialsInterface
    {
        if (!$this->hasComponent($component)) {
            $this->components->add($component);
            $component->setBom($this);
        }

        return $this;
    }

    public function removeComponent(BOMComponentInterface $component): BillOfMaterialsInterface
    {
        if ($this->hasComponent($component)) {
            $this->components->removeElement($component);
            $component->setBom(null);
        }

        return $this;
    }

    /**
     * @param Collection<BOMComponentInterface> $components
     */
    public function setComponents(Collection $components): BillOfMaterialsInterface
    {
        $this->components = $components;

        return $this;
    }
}
