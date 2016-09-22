<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface StateSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StateSubjectInterface
{
    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return $this|StateSubjectInterface
     */
    public function setState($state);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();
}
