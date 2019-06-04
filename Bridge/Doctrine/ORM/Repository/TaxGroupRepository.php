<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TaxGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupRepository extends ResourceRepository implements TaxGroupRepositoryInterface
{
    /**
     * @var TaxGroupInterface
     */
    private $defaultTaxGroup;


    /**
     * @inheritdoc
     */
    public function findDefault(): TaxGroupInterface
    {
        if (null !== $this->defaultTaxGroup) {
            return $this->defaultTaxGroup;
        }

        if (null === $this->defaultTaxGroup = $this->findOneBy(['default' => true])) {
            throw new RuntimeException('Default tax group not found.');
        }

        return $this->defaultTaxGroup;
    }
}
