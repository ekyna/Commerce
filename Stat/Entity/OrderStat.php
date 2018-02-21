<?php

namespace Ekyna\Component\Commerce\Stat\Entity;

/**
 * Class OrderStat
 * @package Ekyna\Component\Commerce\Stat\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStat
{
    const TYPE_YEAR  = 0;
    const TYPE_MONTH = 1;
    const TYPE_DAY   = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $date;

    /**
     * @var float
     */
    private $revenue = 0;

    /**
     * @var float
     */
    private $shipping = 0;

    /**
     * @var float
     */
    private $margin = 0;

    /**
     * @var int
     */
    private $orders = 0;

    /**
     * @var int
     */
    private $items = 0;

    /**
     * @var float
     */
    private $average = 0;

    /**
     * @var \DateTime
     */
    private $updatedAt;


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
     * Sets the id.
     *
     * @param int $id
     *
     * @return OrderStat
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param int $type
     *
     * @return OrderStat
     */
    public function setType($type)
    {
        $this->type = $type;

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
     * @return OrderStat
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the revenue.
     *
     * @return float
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * Sets the revenue.
     *
     * @param float $revenue
     *
     * @return OrderStat
     */
    public function setRevenue($revenue)
    {
        $this->revenue = (float)$revenue;

        return $this;
    }

    /**
     * Returns the shipping.
     *
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Sets the shipping.
     *
     * @param float $shipping
     *
     * @return OrderStat
     */
    public function setShipping($shipping)
    {
        $this->shipping = (float)$shipping;

        return $this;
    }

    /**
     * Returns the margin.
     *
     * @return float
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * Sets the margin.
     *
     * @param float $margin
     *
     * @return OrderStat
     */
    public function setMargin($margin)
    {
        $this->margin = (float)$margin;

        return $this;
    }

    /**
     * Returns the orders count.
     *
     * @return int
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Sets the orders count.
     *
     * @param int $orders
     */
    public function setOrders($orders)
    {
        $this->orders = (int)$orders;
    }

    /**
     * Returns the items count.
     *
     * @return int
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the items count.
     *
     * @param int $items
     *
     * @return OrderStat
     */
    public function setItems($items)
    {
        $this->items = (int)$items;

        return $this;
    }

    /**
     * Returns the averageTotal.
     *
     * @return float
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * Sets the averageTotal.
     *
     * @param float $average
     *
     * @return OrderStat
     */
    public function setAverage($average)
    {
        $this->average = (float)$average;

        return $this;
    }

    /**
     * Returns the updated at datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the updated at datetime.
     *
     * @param \DateTime $updatedAt
     *
     * @return OrderStat
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns the margin in percentage.
     *
     * @return float|int
     */
    public function getMarginPercent()
    {
        if (0 < $this->margin && 0 < $this->revenue) {
            return round($this->margin * 100 / $this->revenue, 1);
        }

        return 0;
    }

    /**
     * Loads the calculation result.
     *
     * @param array $result
     *
     * @return bool Whether a property has changed.
     */
    public function loadResult(array $result)
    {
        $changed = false;

        foreach (['revenue', 'shipping', 'margin', 'orders', 'items', 'average'] as $property) {
            if ($this->{$property} != $result[$property]) {
                $this->{$property} = $result[$property];
                $changed = true;
            }
        }

        return $changed;
    }
}