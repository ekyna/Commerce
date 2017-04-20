<?php

declare(strict_types=1);

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
     * @return bool Whether the document has been updated.
     */
    public function calculate(DocumentInterface $document): bool;
}
