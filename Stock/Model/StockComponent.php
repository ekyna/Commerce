<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockComponent
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockComponent
{
    /**
     * @var StockSubjectInterface
     */
    private $subject;

    /**
     * @var float
     */
    private $quantity;


    /**
     * Constructor.
     *
     * @param StockSubjectInterface $subject
     * @param float                 $quantity
     */
    public function __construct(StockSubjectInterface $subject, float $quantity)
    {
        $this->subject = $subject;
        $this->quantity = $quantity;
    }

    /**
     * Returns the subject.
     *
     * @return StockSubjectInterface
     */
    public function getSubject(): StockSubjectInterface
    {
        return $this->subject;
    }

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }
}
