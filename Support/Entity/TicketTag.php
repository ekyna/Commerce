<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Entity;

use Ekyna\Component\Commerce\Support\Model\TicketTagInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class TicketTag
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TicketTag extends AbstractResource implements TicketTagInterface
{
    private ?string $name;

    public function __toString(): string
    {
        return $this->name ?? 'New ticket tag';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): TicketTag
    {
        $this->name = $name;

        return $this;
    }
}
