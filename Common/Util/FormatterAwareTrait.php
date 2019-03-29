<?php

namespace Ekyna\Component\Commerce\Common\Util;

use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Trait FormatterAwareTrait
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait FormatterAwareTrait
{
    /**
     * @var FormatterFactory
     */
    protected $formatterFactory;

    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * Sets the formatter factory.
     *
     * @param FormatterFactory $formatterFactory
     */
    public function setFormatterFactory(FormatterFactory $formatterFactory)
    {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * Returns the formatter.
     *
     * @return Formatter
     */
    protected function getFormatter()
    {
        if ($this->formatter) {
            return $this->formatter;
        }

        if (!$this->formatterFactory) {
            throw new RuntimeException("Please call setFormatterFactory() first.");
        }

        return $this->formatter = $this->formatterFactory->create();
    }
}
