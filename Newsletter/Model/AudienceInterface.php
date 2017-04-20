<?php

declare(strict_types=1);

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
    public function getGateway(): ?string;

    public function setGateway(string $gateway): AudienceInterface;

    public function getIdentifier(): ?string;

    public function setIdentifier(string $identifier): AudienceInterface;

    public function getName(): ?string;

    public function setName(string $name): AudienceInterface;

    public function isPublic(): bool;

    public function setPublic(bool $public): AudienceInterface;

    public function isDefault(): bool;

    public function setDefault(bool $default): AudienceInterface;

    /**
     * Sets the (translation) title.
     */
    public function setTitle(string $title): AudienceInterface;

    /**
     * Returns the (translation) title.
     */
    public function getTitle(): ?string;
}
