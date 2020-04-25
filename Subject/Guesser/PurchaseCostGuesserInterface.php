<?php

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface PurchaseCostGuesserInterface
 * @package Ekyna\Component\Commerce\Subject\Guesser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PurchaseCostGuesserInterface
{
    /**
     * Guess the purchase cost for the given subject.
     *
     * @param SubjectInterface $subject  The subject
     * @param string           $quote    The quote currency
     * @param bool             $shipping Whether to include shipping cost
     *
     * @return float|null
     */
    public function guess(SubjectInterface $subject, string $quote, bool $shipping = false): ?float;
}
