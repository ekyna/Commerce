<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait NumberSubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait NumberSubjectTrait
{
    /**
     * @var string
     */
    protected $number;


    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return $this|NumberSubjectInterface
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }
}
