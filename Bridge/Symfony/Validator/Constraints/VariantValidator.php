<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class VariantValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($variant, Constraint $constraint)
    {
        if (!$variant instanceof Model\ProductInterface) {
            throw new InvalidArgumentException("Expected instance of ProductInterface");
        }
        if (!$constraint instanceof Variant) {
            throw new InvalidArgumentException("Expected instance of Variant (validation constraint)");
        }

        /* @var Model\ProductInterface $variant */
        /* @var Variant $constraint */

        // Asserts the constraint is applied to a variant.
        Model\ProductTypes::assertVariant($variant);

        $this->validateAttributes($variant, $constraint);
    }

    /**
     * Validates the variant attributes regarding to parent attribute set.
     *
     * @param Model\ProductInterface $variant
     * @param Variant                $constraint
     */
    protected function validateAttributes(Model\ProductInterface $variant, Variant $constraint)
    {
        // Parent is mandatory
        if (null === $parent = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        // Parent attribute set is mandatory
        if (null === $attributeSet = $parent->getAttributeSet()) {
            throw new RuntimeException("Variant's parent attribute set must be defined.");
        }

        $attributes = $variant->getAttributes();
        $validGroups = [];
        $slotsCounts = [];
        $totalCount = 0;

        // Gather attributes count per slot, and total attributes count.
        foreach ($attributeSet->getSlots() as $slot) {
            $count = 0;
            $group = $slot->getGroup();

            foreach ($attributes as $attribute) {
                if ($attribute->getGroup() === $group) {
                    $count++;
                }
            }

            $validGroups[] = $group;
            $slotsCounts[] = [$slot, $count];
            $totalCount += $count;
        }

        foreach ($slotsCounts as $data) {
            /**
             * @var Model\AttributeSlotInterface $slot
             * @var int                          $count
             */
            list($slot, $count) = $data;

            // Asserts that each slot has at least one assigned attribute
            if ($count == 0) {
                $this->context
                    ->buildViolation($constraint->slotAttributeIsMandatory)
                    ->setParameter('%group_name%', $slot->getGroup()->getName())
                    ->atPath('attributes')
                    ->addViolation();

                return;
            }

            // Asserts that non multiple slots do not have more than one assigned attribute
            if (!$slot->isMultiple() && 1 < $count) {
                $this->context
                    ->buildViolation($constraint->slotHasTooManyAttributes)
                    ->setParameter('%group_name%', $slot->getGroup()->getName())
                    ->atPath('attributes')
                    ->addViolation();

                return;
            }
        }

        // Asserts that we gathered every attributes (ie all attributes belongs to a slot group)
        if ($attributes->count() != $totalCount) {
            foreach ($attributes as $attribute) {
                if (!in_array($attribute->getGroup(), $validGroups)) {
                    $this->context
                        ->buildViolation($constraint->unexpectedAttribute)
                        ->setParameter('%attribute_name%', $attribute->getName())
                        ->atPath('attributes')
                        ->addViolation();

                    return;
                }
            }
        }

        // Asserts that the variant is unique (ie no other parent's variant has the same attributes collection)
        $signature = $variant->getUniquenessSignature();
        $variants = $parent->getVariants();
        foreach ($variants as $v) {
            if ($v !== $variant && $v->getUniquenessSignature() === $signature) {
                $this->context
                    ->buildViolation($constraint->variantIsNotUnique)
                    ->atPath('attributes')
                    ->addViolation();

                return;
            }
        }
    }
}
