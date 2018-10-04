<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as BaseConstraint;

/**
 * Phone number constraint.
 *
 * @Annotation
 */
class PhoneNumber extends BaseConstraint
{
    /**
     * Returns whether the given type is valid.
     *
     * @param string $type
     *
     * @return bool
     */
    public function isValidType($type)
    {
        return in_array($type, array(
            self::ANY,
            self::FIXED_LINE,
            self::MOBILE,
            self::PAGER,
            self::PERSONAL_NUMBER,
            self::PREMIUM_RATE,
            self::SHARED_COST,
            self::TOLL_FREE,
            self::UAN,
            self::VOIP,
            self::VOICEMAIL,
        ), true);
    }

    /**
     * Returns the first configured type.
     *
     * @return string
     */
    public function getType()
    {
        if (is_string($this->type)) {
            $type = $this->type;
        } elseif (is_array($this->type)) {
            $type = reset($this->type);
        } else {
            $type = null;
        }

        return $this->isValidType($type) ? $type : self::ANY;
    }

    /**
     * Returns the configured types.
     *
     * @return array
     */
    public function getTypes()
    {
        if (is_string($this->type)) {
            $types = array($this->type);
        } elseif (is_array($this->type)) {
            $types = $this->type;
        } else {
            $types =  array();
        }

        $types = array_filter($types, array($this, 'isValidType'));

        return empty($types) ? array(self::ANY) : $types;
    }

    /**
     * Returns the violation message for the first configured type.
     *
     * @return null|string
     */
    public function getMessage()
    {
        // TODO Deal with multiple types

        if (null !== $this->message) {
            return $this->message;
        }

        switch ($this->getType()) {
            case self::FIXED_LINE:
                return 'This value is not a valid fixed-line number.';
            case self::MOBILE:
                return 'This value is not a valid mobile number.';
            case self::PAGER:
                return 'This value is not a valid pager number.';
            case self::PERSONAL_NUMBER:
                return 'This value is not a valid personal number.';
            case self::PREMIUM_RATE:
                return 'This value is not a valid premium-rate number.';
            case self::SHARED_COST:
                return 'This value is not a valid shared-cost number.';
            case self::TOLL_FREE:
                return 'This value is not a valid toll-free number.';
            case self::UAN:
                return 'This value is not a valid UAN.';
            case self::VOIP:
                return 'This value is not a valid VoIP number.';
            case self::VOICEMAIL:
                return 'This value is not a valid voicemail access number.';
        }

        return 'This value is not a valid phone number.';
    }

    /**
     * @inheritDoc
     */
    public function validatedBy()
    {
        return PhoneNumberValidator::class;
    }
}
