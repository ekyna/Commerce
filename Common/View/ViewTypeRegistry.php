<?php

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ViewTypeRegistry
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ViewTypeRegistry implements ViewTypeRegistryInterface
{
    /**
     * @var array|ViewTypeInterface[]
     */
    private $types;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->types = [];
    }

    /**
     * @inheritDoc
     */
    public function addType(ViewTypeInterface $type): void
    {
        if (array_key_exists($name = $type->getName(), $this->types)) {
            throw new InvalidArgumentException("The view type '{$name}' is already registered.");
        }

        $this->types[$name] = $type;
    }

    /**
     * @inheritDoc
     */
    public function getTypesForSale(Model\SaleInterface $sale): array
    {
        $types = [];

        foreach ($this->types as $type) {
            if ($type->supportsSale($sale)) {
                $types[] = $type;
            }
        }

        usort($types, function(ViewTypeInterface $a, ViewTypeInterface $b) {
            return $a->getPriority() - $b->getPriority();
        });

        return $types;
    }
}
