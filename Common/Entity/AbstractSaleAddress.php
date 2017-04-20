<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\SaleAddressInterface;

/**
 * Class AbstractSaleAddress
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAddress extends AbstractAddress implements SaleAddressInterface
{
    protected ?string $information = null;


    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): SaleAddressInterface
    {
        $this->information = $information;

        return $this;
    }
}
