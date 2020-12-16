<?php

namespace Ekyna\Component\Commerce\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class SupplierTemplate
 * @package Ekyna\Component\Commerce\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method SupplierTemplateTranslationInterface translate($locale = null, $create = false)
 * @method SupplierTemplateTranslationInterface[] getTranslations()
 */
class SupplierTemplate extends AbstractTranslatable implements SupplierTemplateInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->title ?: 'New supplier template';
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): SupplierTemplate
    {
        $this->title = $title;

        return $this;
    }
}
