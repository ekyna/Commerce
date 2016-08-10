<?php

namespace Ekyna\Component\Commerce\Product\EventListener\Handler;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Product\Model;

/**
 * Class HandlerFactory
 * @package Ekyna\Component\Commerce\Product\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HandlerFactory
{
    /**
     * @var array|HandlerInterface
     */
    private $handlers = [];


    /**
     * Returns the handler regarding to the product type.
     *
     * @param Model\ProductInterface $product
     *
     * @return HandlerInterface
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function getHandler(Model\ProductInterface $product)
    {
        if (!Model\ProductTypes::isValidType($type = $product->getType())) {
            throw new Exception\RuntimeException("Product type must be set first.");
        }

        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }

        $handler = null;

        switch ($type) {
            case Model\ProductTypes::TYPE_SIMPLE:
                $handler = new SimpleHandler();
                break;

            case Model\ProductTypes::TYPE_VARIABLE:
                $handler = new VariableHandler();
                break;

            case Model\ProductTypes::TYPE_VARIANT:
                $handler = new VariantHandler();
                break;

            case Model\ProductTypes::TYPE_BUNDLE:
                $handler = new BundleHandler();
                break;

            case Model\ProductTypes::TYPE_CONFIGURABLE:
                $handler = new ConfigurableHandler();
                break;

            default:
                throw new Exception\InvalidArgumentException("Unexpected product type.");
        }

        $handler->setFactory($this);

        $this->handlers[$type] = $handler;

        return $handler;
    }
}
