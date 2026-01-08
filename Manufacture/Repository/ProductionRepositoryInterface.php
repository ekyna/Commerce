<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Manufacture\Repository;

use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ProductionRepositoryInterface
 * @package Ekyna\Component\Commerce\Manufacture\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductionRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @return array<int, ProductionInterface>
     */
    public function findBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range): array;

    /**
     * @return array<int, ProductionInterface>
     */
    public function findByComponentAndDateRange(SubjectInterface $subject, ?DateRange $range): array;
}
