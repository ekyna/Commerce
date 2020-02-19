<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Class AbstractSaleAddress
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAddress extends AbstractAddress implements SaleAddressInterface
{
    /**
     * @var string
     */
    protected $information;


    /**
     * @inheritDoc
     */
    public function getInformation(): ?string
    {
        return $this->information;
    }

    /**
     * @inheritDoc
     */
    public function setInformation(string $information = null): SaleAddressInterface
    {
        $this->information = $information;

        return $this;
    }
}
