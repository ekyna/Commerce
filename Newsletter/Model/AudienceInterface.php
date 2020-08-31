<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\KeySubjectInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface AudienceInterface
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method AudienceTranslationInterface translate($locale = null, $create = false)
 * @method Collection|AudienceTranslationInterface[] getTranslations()
 */
interface AudienceInterface extends TranslatableInterface, KeySubjectInterface, TimestampableInterface
{
    /**
     * Returns the provider.
     *
     * @return string
     */
    public function getGateway(): ?string;

    /**
     * Sets the gateway name.
     *
     * @param string $provider
     *
     * @return $this|AudienceInterface
     */
    public function setGateway(string $provider): AudienceInterface;

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): ?string;

    /**
     * Sets the identifier.
     *
     * @param string $identifier
     *
     * @return $this|AudienceInterface
     */
    public function setIdentifier(string $identifier): AudienceInterface;

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|AudienceInterface
     */
    public function setName(string $name): AudienceInterface;

    /**
     * Returns the public.
     *
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * Sets the public.
     *
     * @param bool $public
     *
     * @return $this|AudienceInterface
     */
    public function setPublic(bool $public): AudienceInterface;

    /**
     * Returns the default.
     *
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Sets the default.
     *
     * @param bool $default
     *
     * @return $this|AudienceInterface
     */
    public function setDefault(bool $default): AudienceInterface;

    /**
     * Sets the (translation) title.
     *
     * @param string $title
     *
     * @return AudienceInterface
     */
    public function setTitle(string $title): AudienceInterface;

    /**
     * Returns the (translation) title.
     *
     * @return string|null
     */
    public function getTitle(): ?string;
}
