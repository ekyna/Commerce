<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\TranslationInterface;

/**
 * Interface MethodTranslationInterface
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface MethodTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(?string $title): MethodTranslationInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): MethodTranslationInterface;
}
