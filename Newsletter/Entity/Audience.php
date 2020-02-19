<?php

namespace Ekyna\Component\Commerce\Newsletter\Entity;

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
    use TimestampableTrait,
        KeySubjectTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $gateway;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $webhook;

    /**
     * @var bool
     */
    protected $public;

    /**
     * @var bool
     */
    protected $default;


    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->webhook   = false;
        $this->public    = false;
        $this->default   = false;
        $this->createdAt = new \DateTime();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return sprintf('[%s] %s', $this->gateway, $this->name);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    /**
     * @inheritDoc
     */
    public function setGateway(string $gateway): AudienceInterface
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): AudienceInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): AudienceInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isWebhook(): bool
    {
        return $this->webhook;
    }

    /**
     * @inheritDoc
     */
    public function setWebhook(bool $webhook): AudienceInterface
    {
        $this->webhook = $webhook;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @inheritDoc
     */
    public function setPublic(bool $public): AudienceInterface
    {
        $this->public = $public;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @inheritDoc
     */
    public function setDefault(bool $default): AudienceInterface
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): AudienceInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }
}
