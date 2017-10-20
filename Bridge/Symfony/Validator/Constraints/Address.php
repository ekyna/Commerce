<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Address
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Address extends Constraint
{
    public $gender_is_mandatory     = 'ekyna_commerce.address.gender_is_mandatory';
    public $first_name_is_mandatory = 'ekyna_commerce.address.first_name_is_mandatory';
    public $last_name_is_mandatory  = 'ekyna_commerce.address.last_name_is_mandatory';
    public $company_is_mandatory    = 'ekyna_commerce.address.company_is_mandatory';
    public $phone_is_mandatory      = 'ekyna_commerce.address.phone_is_mandatory';
    public $mobile_is_mandatory     = 'ekyna_commerce.address.mobile_is_mandatory';

    public $identity                = true;
    public $company                 = false;
    public $phone                   = false;
    public $mobile                  = false;


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
