<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AddressValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * {@inheritdoc}
     */
    public function validate($address, Constraint $constraint)
    {
        if (null === $address) {
            return;
        }

        if (!$address instanceof AddressInterface) {
            throw new UnexpectedTypeException($address, AddressInterface::class);
        }
        if (!$constraint instanceof Address) {
            throw new UnexpectedTypeException($constraint, Address::class);
        }

        $config = [
            'company'    => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 64,
                ]),
            ],
            'street'     => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 128,
                ]),
            ],
            'complement' => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 128,
                ]),
            ],
            'supplement' => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 128,
                ]),
            ],
            'postalCode' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 16,
                ]),
            ],
            'city'       => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 64,
                ]),
            ],
            'country'    => [
                new Assert\NotNull(),
            ],
            /*TODO 'phone'      => [
                new PhoneNumber([
                    'type' => 'fixed_line',
                ]),
            ],
            'mobile'     => [
                new PhoneNumber([
                    'type' => 'mobile',
                ]),
            ],*/
        ];

        if (null !== $country = $address->getCountry()) {
            /*TODO $config['phone'] = [
                new PhoneNumber([
                    'type'          => 'fixed_line',
                    'defaultRegion' => $country->getCode(),
                ]),
            ];
            $config['mobile'] = [
                new PhoneNumber([
                    'type'          => 'mobile',
                    'defaultRegion' => $country->getCode(),
                ]),
            ];*/

            $zipCodeClass = 'ZipCodeValidator\Constraints\ZipCode';
            if (class_exists($zipCodeClass)) {
                $config['postalCode'][] = new $zipCodeClass([
                    'message'     => $constraint->invalid_zip_code,
                    'iso'         => $country->getCode(),
                    'ignoreEmpty' => true,
                    'strict'      => false,
                ]);
            }
        }

        if ($constraint->company) {
            $config['company'][] = new Assert\NotBlank();
        }
        if ($constraint->phone) {
            $config['phone'][] = new Assert\NotBlank();
        }
        if ($constraint->mobile) {
            $config['mobile'][] = new Assert\NotBlank();
        }

        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($config as $field => $constraints) {
            $violationList = $this
                ->context
                ->getValidator()
                ->validate($this->propertyAccessor->getValue($address, $field), $constraints);

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violationList as $violation) {
                $this->context
                    ->buildViolation($violation->getMessage())
                    ->atPath($field)
                    ->addViolation();
            }
        }

        IdentityValidator::validateIdentity($this->context, $address, [
            'required' => $constraint->identity,
        ]);
    }
}
