<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ProductTypes
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypes
{
    const TYPE_SIMPLE       = 'simple';
    const TYPE_VARIABLE     = 'variable';
    const TYPE_VARIANT      = 'variant';
    const TYPE_BUNDLE       = 'bundle';
    const TYPE_CONFIGURABLE = 'configurable';

    /**
     * Returns all the types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_SIMPLE,
            static::TYPE_VARIABLE,
            static::TYPE_VARIANT,
            static::TYPE_BUNDLE,
            static::TYPE_CONFIGURABLE,
        ];
    }

    /**
     * Returns whether the given type is valid or not.
     *
     * @param string $type
     *
     * @return bool
     */
    static public function isValidType($type)
    {
        return in_array($type, static::getTypes(), true);
    }

    /**
     * Asserts that the product has the 'simple' type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static public function assertSimple(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_SIMPLE);
    }

    /**
     * Asserts that the product has the 'variable' type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static public function assertVariable(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_VARIABLE);
    }

    /**
     * Asserts that the product has the 'variant' type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static public function assertVariant(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_VARIANT);
    }

    /**
     * Asserts that the product has the 'bundle' type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static public function assertBundle(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_BUNDLE);
    }

    /**
     * Asserts that the product has the 'configurable' type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static public function assertConfigurable(ProductInterface $product)
    {
        static::assertType($product, static::TYPE_CONFIGURABLE);
    }

    /**
     * Asserts that the product has the given type.
     *
     * @param ProductInterface $product
     * @throws InvalidArgumentException
     */
    static private function assertType(ProductInterface $product, $type)
    {
        if (!($product->getType() === $type)) {
            throw new InvalidArgumentException("Expected product of type '$type'.");
        }
    }
}
