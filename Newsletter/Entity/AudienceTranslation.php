<?php

namespace Ekyna\Component\Commerce\Newsletter\Entity;

use Ekyna\Component\Commerce\Newsletter\Model\AudienceTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class AudienceTranslation
 * @package Ekyna\Component\Commerce\Newsletter\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceTranslation extends AbstractTranslation implements AudienceTranslationInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;


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
    public function setTitle(string $title): AudienceTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description = null): AudienceTranslationInterface
    {
        $this->description = $description;

        return $this;
    }
}
