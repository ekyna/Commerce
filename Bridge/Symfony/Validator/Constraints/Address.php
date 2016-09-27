<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Address
 * @package Ekyna\Bundle\UserBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Address extends Constraint
{
    public $genderIsMandatory    = 'ekyna_commerce.address.gender_is_mandatory';
    public $firstNameIsMandatory = 'ekyna_commerce.address.first_name_is_mandatory';
    public $lastNameIsMandatory  = 'ekyna_commerce.address.last_name_is_mandatory';
    public $companyIsMandatory   = 'ekyna_commerce.address.company_is_mandatory';
    public $phoneIsMandatory     = 'ekyna_commerce.address.phone_is_mandatory';
    public $mobileIsMandatory    = 'ekyna_commerce.address.mobile_is_mandatory';

    public $identity = true;
    public $company  = false;
    public $phone    = false;
    public $mobile   = false;
}
