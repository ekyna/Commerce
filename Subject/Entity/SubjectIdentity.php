<?php

namespace Ekyna\Component\Commerce\Subject\Entity;

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
     * @var array
     */
    private $data;


    /**
     * Returns whether the identity is defined or not.
     *
     * @return bool
     */
    public function isDefined()
    {
        return 0 < $this->id && class_exists($this->class);
    }

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
    public function setId($id = null)
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
    public function setClass($class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Returns the data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data.
     *
     * @param array $data
     *
     * @return SubjectIdentity
     */
    public function setData(array $data = null)
    {
        $this->data = $data;

        return $this;
    }
}
