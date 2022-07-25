<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

/**
 * Trait FormatterAwareTrait
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait FormatterAwareTrait
{
    protected FormatterFactory $formatterFactory;
    private ?Formatter         $formatter = null;

    public function setFormatterFactory(FormatterFactory $formatterFactory): void
    {
        $this->formatterFactory = $formatterFactory;
    }

    protected function getFormatter(): Formatter
    {
        if ($this->formatter) {
            return $this->formatter;
        }

        return $this->formatter = $this->formatterFactory->create();
    }
}
