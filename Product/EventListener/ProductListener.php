<?php

namespace Ekyna\Component\Commerce\Product\EventListener;

use Ekyna\Component\Commerce\Common\Adapter\PersistenceAwareInterface;
use Ekyna\Component\Commerce\Common\Adapter\PersistenceAwareTrait;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Builder\VariantBuilderInterface;
use Ekyna\Component\Commerce\Product\Model\ProductEventInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;

/**
 * Class ProductListener
 * @package Ekyna\Component\Commerce\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductListener
{
    use PersistenceAwareTrait;

    /**
     * @var VariantBuilderInterface
     */
    protected $variantBuilder;


    /**
     * Constructor.
     *
     * @param VariantBuilderInterface $variantBuilder
     */
    public function __construct(VariantBuilderInterface $variantBuilder)
    {
        $this->variantBuilder = $variantBuilder;
    }

    /**
     * Pre create event handler.
     *
     * @param ProductEventInterface $event
     */
    public function onPreCreate(ProductEventInterface $event)
    {
        $product = $event->getProduct();

        $this->handleProduct($product);

        $product
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
    }

    /**
     * Pre update event handler.
     *
     * @param ProductEventInterface $event
     */
    public function onPreUpdate(ProductEventInterface $event)
    {
        $product = $event->getProduct();

        $this->handleProduct($product);

        $product->setUpdatedAt(new \DateTime());
    }

    /**
     * Pre delete event handler.
     *
     * @param ProductEventInterface $event
     */
    public function onPreDelete(ProductEventInterface $event)
    {

    }

    /**
     * Handles the product.
     *
     * @param ProductInterface $product
     */
    protected function handleProduct(ProductInterface $product)
    {
        if (null === $type = $product->getType()) {
            throw new RuntimeException("Product type must be set first.");
        }

        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
                $this->handleSimple($product);
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->handleVariable($product);
                break;
            case ProductTypes::TYPE_VARIANT:
                $this->handleVariant($product);
                break;
            case ProductTypes::TYPE_BUNDLE:
                $this->handleBundle($product);
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->handleConfigurable($product);
                break;
            default:
                throw new InvalidArgumentException("Unexpected product type.");
        }
    }

    /**
     * Handles the simple product.
     *
     * @param ProductInterface $simple
     */
    protected function handleSimple(ProductInterface $simple)
    {

    }

    /**
     * Handles the variable product.
     *
     * @param ProductInterface $variable
     */
    protected function handleVariable(ProductInterface $variable)
    {
        $this->variantBuilder->updateVariableMinPrice($variable);
    }

    /**
     * Handles the variant product.
     *
     * @param ProductInterface $variant
     */
    protected function handleVariant(ProductInterface $variant)
    {
        if (0 === strlen($variant->getDesignation())) {
            $this->variantBuilder->buildDesignation($variant);
        }

        if (null === $parent = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be set first.");
        }

        $this->variantBuilder
            ->inheritVariableTaxGroup($variant)
            ->updateVariableMinPrice($parent);
    }

    /**
     * Handles the bundle product.
     *
     * @param ProductInterface $bundle
     */
    protected function handleBundle(ProductInterface $bundle)
    {

    }

    /**
     * Handles the configurable product.
     *
     * @param ProductInterface $configurable
     */
    protected function handleConfigurable(ProductInterface $configurable)
    {

    }
}
