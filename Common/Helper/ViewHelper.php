<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Helper;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Adjustment;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

use function array_map;
use function implode;
use function is_null;

/**
 * Class ViewHelper
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ViewHelper
{
    public function __construct(
        private readonly Formatter $formatter,
    ) {
    }

    public function buildIncludesDescription(Amount $result, ?Decimal $divisor): string
    {
        if (is_null($divisor)) {
            $formatter = function (Adjustment $adjustment) {
                return $this->formatter->currency($adjustment->getAmount());
            };
        } else {
            if (!$divisor instanceof Decimal) {
                throw new UnexpectedTypeException($divisor, Decimal::class);
            }
            $formatter = function (Adjustment $adjustment) use ($divisor) {
                return $this->formatter->currency($adjustment->getAmount()->div($divisor));
            };
        }

        $callback = static function (Adjustment $adjustment) use ($formatter) {
            return $adjustment->getName() . ' ' . $formatter($adjustment);
        };

        return implode('. ', array_map($callback, $result->getIncludedAdjustments()));
    }
}
