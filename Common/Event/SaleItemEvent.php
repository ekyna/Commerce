<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SaleItemEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEvent extends Event
{
    /**
     * @var SaleItemInterface
     */
    private $item;

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param SaleItemInterface $item
     * @param array             $data
     */
    public function __construct(SaleItemInterface $item, array $data = [])
    {
        $this->item = $item;
        $this->data = $data;
    }

    /**
     * Returns the item.
     *
     * @return SaleItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Returns the data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
