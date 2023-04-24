<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Generator;

use DateTime;

/**
 * Class DateNumberGenerator
 * @package Ekyna\Component\Commerce\Common\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DateNumberGenerator extends AbstractGenerator
{
    /**
     * @inheritDoc
     */
    protected function getPrefix(): string
    {
        return (new DateTime())->format($this->prefix);
    }
}
