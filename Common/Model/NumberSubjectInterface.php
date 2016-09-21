<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface NumberSubjectInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface NumberSubjectInterface
{
    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber();

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|NumberSubjectInterface
     */
    public function setNumber($number);
}
