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
    public function setState(string $state);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState(): ?string;
}
