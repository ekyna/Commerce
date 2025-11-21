<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity as Identity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface as Subject;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface as Reference;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class BillOfMaterialsRepository
 * @package Ekyna\Component\Commerce\Manufacture\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BillOfMaterialsRepositoryInterface extends ResourceRepositoryInterface
{
    public function findNewVersion(BillOfMaterialsInterface $bom): ?BillOfMaterialsInterface;

    public function findOneValidatedBySubject(Reference|Subject|Identity $identity): ?BillOfMaterialsInterface;

    /**
     * @return array<int, BillOfMaterialsInterface>
     */
    public function findBySubject(Reference|Subject|Identity $identity): array;

    public function findOneValidatedByComponentWithSubject(
        Reference|Subject|Identity $identity
    ): ?BillOfMaterialsInterface;

    /**
     * @return array<int, BillOfMaterialsInterface>
     */
    public function findByComponentWithSubject(Reference|Subject|Identity $identity): array;

    /**
     * @return array<int, BillOfMaterialsInterface>
     */
    public function findValidatedByComponentWithSubject(Reference|Subject|Identity $identity): array;
}
