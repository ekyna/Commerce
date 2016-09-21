<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface KeySubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface KeySubjectInterface
{
    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey();

    /**
     * Sets the key.
     *
     * @param string $key
     *
     * @return $this|KeySubjectInterface
     */
    public function setKey($key);
}
