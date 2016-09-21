<?php

namespace Ekyna\Component\Commerce\Quote\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractAddress;
use Ekyna\Component\Commerce\Quote\Model\QuoteAddressInterface;

/**
 * Class QuoteAddress
 * @package Ekyna\Component\Commerce\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddress extends AbstractAddress implements QuoteAddressInterface
{
    /**
     * @var int
     */
    protected $id;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
}
