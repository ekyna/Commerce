<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Class Genders
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Genders
{
    public const GENDER_MR   = 'mr';
    public const GENDER_MRS  = 'mrs';
    public const GENDER_MISS = 'miss';


    public static function getGenders(): array
    {
        return [
            Genders::GENDER_MR,
            Genders::GENDER_MRS,
            Genders::GENDER_MISS,
        ];
    }

    public static function isValidGender(string $gender): bool
    {
        return in_array($gender, Genders::getGenders(), true);
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
