<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait StateSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait StateSubjectTrait
{
    /**
     * @var string
     */
    protected $state;


    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|StateSubjectInterface
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
