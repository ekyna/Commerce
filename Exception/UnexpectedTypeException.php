<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Exception;

use Throwable;
use UnexpectedValueException as BaseException;

use function get_class;
use function gettype;
use function is_object;

/**
 * Class UnexpectedTypeException
 * @package Ekyna\Component\Commerce\Exception
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnexpectedTypeException extends BaseException implements CommerceExceptionInterface
{
    /**
     * Constructor.
     *
     * @param mixed           $value
     * @param string|string[] $types
     * @param int             $code
     * @param Throwable|null  $previous
     */
    public function __construct($value, $types, $code = 0, Throwable $previous = null)
    {
        $types = (array)$types;

        if (1 === $length = count($types)) {
            $types = reset($types);
        } elseif (2 === $length) {
            $types = implode(' or ', $types);
        } else {
            $types = implode(', ', array_slice($types, 0, $length - 2)) . ' or ' . $types[$length - 1];
        }

        $message = sprintf('Expected %s, got %s', $types, is_object($value) ? get_class($value) : gettype($value));

        parent::__construct($message, $code, $previous);
    }
}
