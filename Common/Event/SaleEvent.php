<?php

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SaleEvent
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleEvent extends Event
{
    use AdjustmentDataTrait;

    /**
     * @var SaleInterface
     */
    private $sale;


    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     */
    public function __construct(SaleInterface $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale()
    {
        return $this->sale;
    }
}
