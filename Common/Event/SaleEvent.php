<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SaleEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleEvent extends Event
{
    use AdjustmentDataTrait;

    private SaleInterface $sale;


    public function __construct(SaleInterface $sale)
    {
        $this->sale = $sale;
    }

    public function getSale(): SaleInterface
    {
        return $this->sale;
    }
}
