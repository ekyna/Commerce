<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Factory;

use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class SupplierProductFactory
 * @package Ekyna\Component\Commerce\Supplier\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductFactory extends ResourceFactory implements SupplierProductFactoryInterface
{
    private TaxGroupRepositoryInterface $taxGroupRepository;

    public function __construct(TaxGroupRepositoryInterface $taxGroupRepository)
    {
        $this->taxGroupRepository = $taxGroupRepository;
    }

    /**
     * @eturn SupplierProductInterface
     */
    public function create(): ResourceInterface
    {
        $product = parent::create();

        $product->setTaxGroup($this->taxGroupRepository->findDefault());

        return $product;
    }
}
