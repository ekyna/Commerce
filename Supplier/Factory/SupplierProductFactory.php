<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Factory;

use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierProductFactory
 * @package Ekyna\Component\Commerce\Supplier\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductFactory extends ResourceFactory implements SupplierProductFactoryInterface
{
    public function __construct(
        private readonly TaxGroupRepositoryInterface $taxGroupRepository,
        private readonly SubjectHelperInterface $subjectHelper,
    ) {
    }

    /**
     * @return SupplierProductInterface
     */
    public function create(): ResourceInterface
    {
        $product = parent::create();

        $product->setTaxGroup($this->taxGroupRepository->findDefault());

        return $product;
    }

    public function createWithSubjectAndSupplier(
        ?SupplierInterface $supplier,
        ?SubjectInterface  $subject
    ): SupplierProductInterface {
        $product = $this->create();

        if ($supplier) {
            $product->setSupplier($supplier);
        }

        if ($subject) {
            $this->setSubject($product, $subject);
        }

        return $product;
    }

    private function setSubject(SupplierProductInterface $product, SubjectInterface $subject): void
    {
        $this->subjectHelper->assign($product, $subject);

        if (empty($product->getDesignation()) && !empty($subject->getDesignation())) {
            $product->setDesignation($subject->getDesignation());
        }

        if (!$product->getTaxGroup()) {
            $product->setTaxGroup($subject->getTaxGroup());
        }

        if (!$subject instanceof StockSubjectInterface) {
            return;
        }

        if ($product->getWeight()->isZero()) {
            $product->setWeight(clone $subject->getPackageWeight());
        }

        if (empty($product->getUnit())) {
            $product->setUnit($subject->getUnit());
        }

        $product->setPhysical($subject->isPhysical());
    }
}
