<?php

namespace Ekyna\Component\Commerce\Document\Model;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class DocumentTypes
 * @package Ekyna\Component\Commerce\Document\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentTypes
{
    const TYPE_FORM         = 'form';
    const TYPE_QUOTE        = 'quote';
    const TYPE_PROFORMA     = 'proforma';
    const TYPE_CONFIRMATION = 'confirmation';


    /**
     * Returns the invoice types.
     *
     * @return array
     */
    static public function getTypes()
    {
        return [
            static::TYPE_FORM,
            static::TYPE_QUOTE,
            static::TYPE_PROFORMA,
            static::TYPE_CONFIRMATION,
        ];
    }

    /**
     * Returns the sale class supported by the given type.
     *
     * @param string $type
     *
     * @return string
     */
    static public function getClass($type)
    {
        switch ($type) {
            case static::TYPE_FORM:
                return CartInterface::class;
            case static::TYPE_QUOTE:
                return QuoteInterface::class;
            case static::TYPE_PROFORMA:
            case static::TYPE_CONFIRMATION:
                return OrderInterface::class;
            default:
                throw new InvalidArgumentException("Unexpected type '$type'.");
        }
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
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}