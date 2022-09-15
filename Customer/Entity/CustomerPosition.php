<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Customer\Entity;

use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class CustomerPosition
 * @package Ekyna\Component\Commerce\Customer\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerPosition extends AbstractResource
{
    private ?string $name;

    public function __toString(): string
    {
        return $this->name ?: 'New customer position';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): CustomerPosition
    {
        $this->name = $name;

        return $this;
    }
}
