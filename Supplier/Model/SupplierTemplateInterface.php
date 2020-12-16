<?php

namespace Ekyna\Component\Commerce\Supplier\Model;


use Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplate;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface SupplierTemplateInterface
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method SupplierTemplateTranslationInterface translate($locale = null, $create = false)
 * @method SupplierTemplateTranslationInterface[] getTranslations()
 */
interface SupplierTemplateInterface extends TranslatableInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int;

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): ?string;

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return SupplierTemplate
     */
    public function setTitle(string $title): SupplierTemplate;
}
