<?php

declare(strict_types=1);

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
    private ?string $title = null;

    public function __toString(): string
    {
        return $this->title ?: 'New supplier template';
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): SupplierTemplate
    {
        $this->title = $title;

        return $this;
    }
}
