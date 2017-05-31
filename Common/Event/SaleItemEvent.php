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
     * Constructor.
     *
     * @param SaleItemInterface $item
     */
    public function __construct(SaleItemInterface $item)
    {
        $this->item = $item;
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
}
