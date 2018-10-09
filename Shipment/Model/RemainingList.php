<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class RemainingList
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RemainingList
{
    /**
     * @var \DateTime
     */
    private $estimatedShippingDate;

    /**
     * @var RemainingEntry[]
     */
    private $entries;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->entries = [];
    }

    /**
     * Returns the "estimated shipping date".
     *
     * @return \DateTime
     */
    public function getEstimatedShippingDate()
    {
        return $this->estimatedShippingDate;
    }

    /**
     * Sets the "estimated shipping date".
     *
     * @param \DateTime $date
     *
     * @return RemainingList
     */
    public function setEstimatedShippingDate(\DateTime $date = null)
    {
        $this->estimatedShippingDate = $date;

        return $this;
    }

    /**
     * Returns the entries.
     *
     * @return RemainingEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Adds the entry.
     *
     * @param RemainingEntry $entry
     *
     * @return RemainingList
     */
    public function addEntry(RemainingEntry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }
}
