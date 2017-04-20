<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface NumberSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NumberSubjectInterface
{
    public function getNumber(): ?string;

    public function setNumber(string $number): NumberSubjectInterface;
}
