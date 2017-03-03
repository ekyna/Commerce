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
     * @inheritdoc
     */
    public function addType(ViewTypeInterface $type)
    {
        if (array_key_exists($name = $type->getName(), $this->types)) {
            throw new InvalidArgumentException("The view type '{$name}' is already registerd.");
        }

        $this->types[$name] = $type;
    }

    /**
     * @inheritdoc
     */
    public function getTypesForSale(Model\SaleInterface $sale)
    {
        $types = [];

        foreach ($this->types as $type) {
            if ($type->supportsSale($sale)) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
