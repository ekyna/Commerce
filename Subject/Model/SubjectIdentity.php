<?php

namespace Ekyna\Component\Commerce\Subject\Model;

/**
 * Class SubjectIdentity
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectIdentity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $class;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return SubjectIdentity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns the class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the class.
     *
     * @param string $class
     *
     * @return SubjectIdentity
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }
}
