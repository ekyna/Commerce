<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AddressValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressValidator extends ConstraintValidator
{
    private ?PropertyAccessor $propertyAccessor = null;

    /**
     * @inheritDoc
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
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
                    'max' => 35,
                ]),
            ],
            'street'     => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 35,
                ]),
            ],
            'complement' => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 35,
                ]),
            ],
            'supplement' => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 35,
                ]),
            ],
            'extra'      => [
                new Assert\Length([
                    'min' => 2,
                    'max' => 35,
                ]),
            ],
            'postalCode' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 10,
                ]),
            ],
            'city'       => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 35,
                ]),
            ],
            'country'    => [
                new Assert\NotNull(),
            ],
            'digicode1'  => [
                new Assert\Length([
                    'max' => 8,
                ]),
            ],
            'digicode2'  => [
                new Assert\Length([
                    'max' => 8,
                ]),
            ],
            'intercom'   => [
                new Assert\Length([
                    'max' => 10,
                ]),
            ],
        ];

        if (null !== $country = $address->getCountry()) {
            $config['phone'] = [
                new PhoneNumber([
                    'type'          => ['fixed_line', 'voip'],
                    'defaultRegion' => $country->getCode(),
                ]),
            ];
            $config['mobile'] = [
                new PhoneNumber([
                    'type'          => 'mobile',
                    'defaultRegion' => $country->getCode(),
                ]),
            ];

            $zipCodeClass = 'ZipCodeValidator\Constraints\ZipCode';
            if (class_exists($zipCodeClass)) {
                $config['postalCode'][] = new $zipCodeClass([
                    'message' => $constraint->invalid_zip_code,
                    'iso'     => $country->getCode(),
                    'strict'  => false,
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

            if (0 === $violationList->count()) {
                continue;
            }

            /** @var ConstraintViolationInterface $violation */
            foreach ($violationList as $violation) {
                $this->context
                    ->buildViolation($violation->getMessage())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->atPath($field)
                    ->addViolation();
            }

            break;
        }

        IdentityValidator::validateIdentity($this->context, $address, [
            'required' => $constraint->identity,
        ]);
    }
}
