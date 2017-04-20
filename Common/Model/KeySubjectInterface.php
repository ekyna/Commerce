<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface KeySubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface KeySubjectInterface
{
    public function getKey(): ?string;

    public function setKey(?string $key): KeySubjectInterface;
}
