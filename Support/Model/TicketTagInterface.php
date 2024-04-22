<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Model;


use Ekyna\Component\Commerce\Support\Entity\TicketTag;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class TicketTag
 * @package Ekyna\Component\Commerce\Support\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TicketTagInterface extends ResourceInterface
{
    public function getName(): ?string;

    public function setName(?string $name): TicketTag;
}
