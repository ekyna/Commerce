<?php

namespace Ekyna\Component\Commerce\Stock\Repository;

use Ekyna\Component\Commerce\Stock\Entity\ResupplyAlert;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Class ResupplyAlertRepository
 * @package Ekyna\Component\Commerce\Stock\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ResupplyAlertRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the resupply alert by email and subject.
     *
     * @param string           $email
     * @param SubjectInterface $subject
     *
     * @return ResupplyAlert|null
     */
    public function findByEmailAndSubject(string $email, SubjectInterface $subject): ?ResupplyAlert;
}
