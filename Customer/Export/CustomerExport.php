<?php

namespace Ekyna\Component\Commerce\Customer\Export;

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
    /**
     * @var \DateTime
     */
    private $from;

    /**
     * @var \DateTime
     */
    private $to;

    /**
     * @var Collection|CustomerGroupInterface[]
     */
    private $groups;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Returns the from.
     *
     * @return \DateTime
     */
    public function getFrom(): ?\DateTime
    {
        return $this->from;
    }

    /**
     * Sets the from.
     *
     * @param \DateTime $from
     *
     * @return CustomerExport
     */
    public function setFrom(\DateTime $from = null): CustomerExport
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Returns the to.
     *
     * @return \DateTime
     */
    public function getTo(): ?\DateTime
    {
        return $this->to;
    }

    /**
     * Sets the to.
     *
     * @param \DateTime $to
     *
     * @return CustomerExport
     */
    public function setTo(\DateTime $to = null): CustomerExport
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Returns the groups.
     *
     * @return Collection|CustomerGroupInterface[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this
     */
    public function addGroup(CustomerGroupInterface $group): CustomerExport
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this
     */
    public function removeGroup(CustomerGroupInterface $group): CustomerExport
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }
}