<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeTrait;
use Ekyna\Component\Resource\Model\SortableTrait;

use function json_encode;
use function md5;

/**
 * Class AbstractSaleItem
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleItem implements Model\SaleItemInterface
{
    use Model\AdjustableTrait;
    use SortableTrait;
    use SubjectRelativeTrait;

    protected ?Model\SaleItemInterface $parent = null;
    /** @var Collection<int, Model\SaleItemInterface> */
    protected Collection $children;
    protected ?string    $description  = null;
    protected Decimal    $quantity;
    protected bool       $compound     = false;
    protected bool       $immutable    = false;
    protected bool       $configurable = false;
    protected bool       $private      = false;
    protected array      $data         = [];


    public function __construct()
    {
        $this->initializeAdjustments();
        $this->initializeSubjectRelative();

        $this->children = new ArrayCollection();
        $this->quantity = new Decimal(1);
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->designation ?: $this->reference ?: 'New sale item';
    }

    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    public function getParent(): ?Model\SaleItemInterface
    {
        return $this->parent;
    }

    public function getPublicParent(): Model\SaleItemInterface
    {
        if (!$this->isPrivate()) {
            return $this;
        }

        $parent = $this;
        do {
            $parent = $parent->getParent();
        } while ($parent->isPrivate());

        return $parent;
    }

    public function setParent(?Model\SaleItemInterface $parent): Model\SaleItemInterface
    {
        $parent && $this->assertItemClass($parent);

        if ($parent === $this->parent) {
            return $this;
        }

        if ($previous = $this->parent) {
            $this->parent = null;
            $previous->removeChild($this);
        }

        if ($this->parent = $parent) {
            $this->parent->addChild($this);
        }

        return $this;
    }

    public function hasChildren(): bool
    {
        return 0 < $this->children->count();
    }

    public function createChild(): Model\SaleItemInterface
    {
        $child = new static();

        $this->addChild($child);

        return $child;
    }

    public function hasChild(Model\SaleItemInterface $child): bool
    {
        $this->assertItemClass($child);

        return $this->children->contains($child);
    }

    public function addChild(Model\SaleItemInterface $child): Model\SaleItemInterface
    {
        $this->assertItemClass($child);

        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Model\SaleItemInterface $child): Model\SaleItemInterface
    {
        $this->assertItemClass($child);

        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function hasAdjustment(Model\AdjustmentInterface $adjustment): bool
    {
        $this->assertItemAdjustmentClass($adjustment);

        return $this->adjustments->contains($adjustment);
    }

    public function addAdjustment(Model\AdjustmentInterface $adjustment): Model\AdjustableInterface
    {
        $this->assertItemAdjustmentClass($adjustment);

        /** @var Model\SaleItemAdjustmentInterface $adjustment */
        if (!$this->adjustments->contains($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setItem($this);
        }

        return $this;
    }

    public function removeAdjustment(Model\AdjustmentInterface $adjustment): Model\AdjustableInterface
    {
        $this->assertItemAdjustmentClass($adjustment);

        /** @var AbstractSaleItemAdjustment $adjustment */
        if ($this->adjustments->contains($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setItem(null);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Model\SaleItemInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(Decimal $quantity): Model\SaleItemInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isCompound(): bool
    {
        return $this->compound;
    }

    public function setCompound(bool $compound): Model\SaleItemInterface
    {
        $this->compound = $compound;

        return $this;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    public function setImmutable(bool $immutable): Model\SaleItemInterface
    {
        $this->immutable = $immutable;

        return $this;
    }

    public function isConfigurable(): bool
    {
        return $this->configurable;
    }

    public function setConfigurable(bool $configurable): Model\SaleItemInterface
    {
        $this->configurable = $configurable;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): Model\SaleItemInterface
    {
        $this->private = $private;

        return $this;
    }

    public function hasPrivateChildren(): bool
    {
        foreach ($this->children as $child) {
            if ($child->isPrivate()) {
                return true;
            }
        }

        return false;
    }

    public function hasPublicChildren(): bool
    {
        foreach ($this->children as $child) {
            if (!$child->isPrivate()) {
                return true;
            }
        }

        return false;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): SaleItemInterface
    {
        $this->data = $data;

        return $this;
    }

    public function hasDatum(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function getDatum(string $key): string|int|null
    {
        return $this->data[$key] ?? null;
    }

    public function setDatum(string $key, string|int $data): Model\SaleItemInterface
    {
        $this->data[$key] = $data;

        return $this;
    }

    public function unsetDatum(string $key): Model\SaleItemInterface
    {
        unset($this->data[$key]);

        return $this;
    }

    public function getLevel(): int
    {
        $level = 0;

        $parent = $this;
        while (null !== $parent = $parent->getParent()) {
            $level++;
        }

        return $level;
    }

    public function getRoot(): ?Model\SaleItemInterface
    {
        $item = $this;

        while (null !== $parent = $item->getParent()) {
            $item = $parent;
        }

        return $item;
    }

    public function getRootSale(): ?Model\SaleInterface
    {
        $item = $this;

        do {
            if ($sale = $item->getSale()) {
                return $sale;
            }

            $item = $item->getParent();
        } while (null !== $item);

        return null;
    }

    public function getParentsQuantity(): Decimal
    {
        $modifier = new Decimal(1);

        $item = $this;
        while (null !== $item = $item->getParent()) {
            $modifier = $modifier->mul($item->getQuantity());
        }

        return $modifier;
    }

    public function getTotalQuantity(): Decimal
    {
        return $this->getQuantity()->mul($this->getParentsQuantity());
    }

    public function isLast(): bool
    {
        if (null !== $this->parent) {
            return $this->position === $this->parent->getChildren()->last()->getPosition();
        }

        return $this->position === $this->getRootSale()->getItems()->last()->getPosition();
    }

    public function getHash(): string
    {
        return md5(json_encode($this->getHashData()));
    }

    private function getHashData(): array
    {
        $data = [
            'r' => $this->reference,
        ];

        if ($this->hasSubjectIdentity()) {
            $data['p'] = $this->subjectIdentity->getProvider();
            $data['i'] = $this->subjectIdentity->getIdentifier();
        }

        if (!empty($this->data)) {
            $data['d'] = $this->data;
        }

        if (null !== $this->parent) {
            $data['q'] = $this->quantity->toFixed(3); // TODO Packaging format
        }

        if (0 < $this->children->count()) {
            $data['c'] = [];
            foreach ($this->children as $child) {
                $data['c'][] = $child->getHashData();
            }
        }

        return $data;
    }

    /**
     * Asserts that the given sale is an instance of the expected class.
     */
    abstract protected function assertSaleClass(Model\SaleInterface $sale): void;

    /**
     * Asserts that the given sale item is an instance of the expected class.
     */
    abstract protected function assertItemClass(Model\SaleItemInterface $child): void;

    /**
     * Asserts that the given adjustment is an instance of the expected class.
     */
    abstract protected function assertItemAdjustmentClass(Model\AdjustmentInterface $adjustment): void;
}
