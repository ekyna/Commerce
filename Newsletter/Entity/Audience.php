<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\KeySubjectTrait;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceTranslationInterface;
use Ekyna\Component\Resource\Model\AbstractTranslatable;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Audience
 * @package Ekyna\Component\Commerce\Newsletter\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method AudienceTranslationInterface translate($locale = null, $create = false)
 * @method Collection|AudienceTranslationInterface[] getTranslations()
 */
class Audience extends AbstractTranslatable implements AudienceInterface
{
    use KeySubjectTrait;
    use TimestampableTrait;

    protected ?string $gateway    = null;
    protected ?string $identifier = null;
    protected ?string $name       = null;
    protected bool    $public     = false;
    protected bool    $default    = false;

    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new DateTime();
    }

    public function __toString(): string
    {
        if ($this->gateway && $this->name) {
            return sprintf('[%s] %s', $this->gateway, $this->name);
        }

        return 'New audience';
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): AudienceInterface
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): AudienceInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): AudienceInterface
    {
        $this->name = $name;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): AudienceInterface
    {
        $this->public = $public;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): AudienceInterface
    {
        $this->default = $default;

        return $this;
    }

    public function setTitle(string $title): AudienceInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }
}
