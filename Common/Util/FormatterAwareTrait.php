<?php

namespace Ekyna\Component\Commerce\Common\Util;

/**
 * Trait FormatterAwareTrait
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait FormatterAwareTrait
{
    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * Sets the formatter.
     *
     * @param Formatter $formatter
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }
}
