<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Guesser;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface SubjectCostGuesserInterface
 * @package Ekyna\Component\Commerce\Subject\Guesser
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectCostGuesserInterface
{
    /**
     * Guess the purchase cost for the given subject.
     *
     * @param SubjectInterface $subject The subject
     */
    public function guess(SubjectInterface $subject): ?Cost;
}
