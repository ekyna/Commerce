<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Export;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class CustomerExport
 * @package Ekyna\Component\Commerce\Customer\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerExport
{
    private DateTimeInterface $from;
    private DateTimeInterface $to;
    /** @var Collection<int, CustomerGroupInterface> */
    private Collection $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getFrom(): ?DateTimeInterface
    {
        return $this->from;
    }

    public function setFrom(?DateTimeInterface $from): CustomerExport
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?DateTimeInterface
    {
        return $this->to;
    }

    public function setTo(?DateTimeInterface $to): CustomerExport
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return Collection<int, CustomerGroupInterface>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addGroup(CustomerGroupInterface $group): CustomerExport
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    public function removeGroup(CustomerGroupInterface $group): CustomerExport
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }
}
