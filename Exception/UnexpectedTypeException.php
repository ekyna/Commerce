<?php

namespace Ekyna\Component\Commerce\Exception;

/**
 * Class UnexpectedTypeException
 * @package Ekyna\Component\Commerce\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedTypeException extends \UnexpectedValueException implements CommerceExceptionInterface
{
    /**
     * @inheritDoc
     */
    public function __construct($value, $types, $code = 0, \Throwable $previous = null)
    {
        $types = (array) $types;

        if (1 === $length = count($types)) {
            $types = $types[0];
        } else {
            $types = implode(', ', array_slice($types, 0, $length - 2)) . ' or ' . $types[$length - 1];
        }

        parent::__construct(sprintf("Expected %s, got %s", $types, gettype($value)), $code, $previous);
    }
}