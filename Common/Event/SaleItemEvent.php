<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SaleItemEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEvent extends Event
{
    use AdjustmentDataTrait;

    private SaleItemInterface $item;


    public function __construct(SaleItemInterface $item)
    {
        $this->item = $item;
    }

    public function getItem(): SaleItemInterface
    {
        return $this->item;
    }
}
