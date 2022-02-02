<?php

declare(strict_types=1);

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
    public function getTitle(): ?string;

    public function setTitle(?string $title): SupplierTemplate;
}
