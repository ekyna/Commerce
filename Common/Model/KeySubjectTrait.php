<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait KeySubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait KeySubjectTrait
{
    protected ?string $key = null;


    public function getKey(): ?String
    {
        return $this->key;
    }

    /**
     * @return $this|KeySubjectInterface
     */
    public function setKey(?string $key): KeySubjectInterface
    {
        $this->key = $key;

        return $this;
    }
}
