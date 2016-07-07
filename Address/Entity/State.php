<?php

namespace Ekyna\Component\Commerce\Address\Entity;

use Ekyna\Component\Commerce\Address\Model\CountryInterface;
use Ekyna\Component\Commerce\Address\Model\StateInterface;

/**
 * Class State
 * @package Ekyna\Component\Commerce\Address\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class State implements StateInterface
{
    /**
     * @var CountryInterface
     */
    protected $country;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $code;


    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @inheritdoc
     */
    public function setCountry(CountryInterface $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
