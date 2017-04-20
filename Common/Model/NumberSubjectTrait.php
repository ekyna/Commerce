<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait NumberSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait NumberSubjectTrait
{
    protected ?string $number = null;


    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @return $this|NumberSubjectInterface
     */
    public function setNumber(string $number): NumberSubjectInterface
    {
        $this->number = $number;

        return $this;
    }
}
