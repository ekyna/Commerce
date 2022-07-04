<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Entity\ResupplyAlert;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class ResupplyAlertRepository
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ResupplyAlert>
 */
interface ResupplyAlertRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the resupply alert by email and subject.
     */
    public function findByEmailAndSubject(string $email, SubjectInterface $subject): ?ResupplyAlert;
}
