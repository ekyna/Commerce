<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait StateSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait StateSubjectTrait
{
    protected string $state;


    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return $this|StateSubjectInterface
     */
    public function setState(string $state): StateSubjectInterface
    {
        $this->state = $state;

        return $this;
    }
}
