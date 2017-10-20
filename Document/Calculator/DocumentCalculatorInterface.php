<?php

namespace Ekyna\Component\Commerce\Document\Calculator;

use Ekyna\Component\Commerce\Document\Model\DocumentInterface;

/**
 * Interface DocumentCalculatorInterface
 * @package Ekyna\Component\Commerce\Document\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DocumentCalculatorInterface
{
    /**
     * Calculates the document.
     *
     * @param DocumentInterface $document
     *
     * @return bool Whether or not the document has been updated.
     */
    public function calculate(DocumentInterface $document);
}
