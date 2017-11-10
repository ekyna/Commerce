<?php

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class Genders
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Genders
{
    const GENDER_MR = 'mr';
    const GENDER_MRS = 'mrs';
    const GENDER_MISS = 'miss';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getGenders()
    {
        return [
            static::GENDER_MR,
            static::GENDER_MRS,
            static::GENDER_MISS,
        ];
    }

    /**
     * Returns whether the given gender is valid or not.
     *
     * @param string $gender
     *
     * @return bool
     */
    static public function isValidGender($gender)
    {
        return in_array($gender, static::getGenders(), true);
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
