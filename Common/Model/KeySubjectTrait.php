<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait KeySubjectTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait KeySubjectTrait
{
    /**
     * @var string
     */
    protected $key;


    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the key.
     *
     * @param string $key
     *
     * @return $this|KeySubjectInterface
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }
}
