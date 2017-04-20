<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\MethodTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AbstractMethodTranslation
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMethodTranslation extends AbstractTranslation implements MethodTranslationInterface
{
    protected ?string $title       = null;
    protected ?string $description = null;


    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): MethodTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): MethodTranslationInterface
    {
        $this->description = $description;

        return $this;
    }
}
