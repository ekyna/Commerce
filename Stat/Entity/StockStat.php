<?php

namespace Ekyna\Component\Commerce\Stat\Entity;

/**
 * Class StockStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockStat
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $inValue = 0;

    /**
     * @var float
     */
    private $soldValue = 0;

    /**
     * @var string
     */
    private $date;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the inValue.
     *
     * @return float
     */
    public function getInValue()
    {
        return $this->inValue;
    }

    /**
     * Sets the inValue.
     *
     * @param float $inValue
     *
     * @return StockStat
     */
    public function setInValue($inValue)
    {
        $this->inValue = (float)$inValue;

        return $this;
    }

    /**
     * Returns the soldValue.
     *
     * @return float
     */
    public function getSoldValue()
    {
        return $this->soldValue;
    }

    /**
     * Sets the soldValue.
     *
     * @param float $soldValue
     *
     * @return StockStat
     */
    public function setSoldValue($soldValue)
    {
        $this->soldValue = (float)$soldValue;

        return $this;
    }

    /**
     * Returns the date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the date.
     *
     * @param string $date
     *
     * @return StockStat
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }
}
