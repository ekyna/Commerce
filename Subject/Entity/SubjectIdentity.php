<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Entity;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectIdentity
 * @package Ekyna\Component\Commerce\Subject\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SubjectIdentity
{
    private ?string           $provider;
    private ?int              $identifier;
    private ?SubjectInterface $subject = null;


    public static function fromSubject(SubjectInterface $subject): self
    {
        return new self(
            $subject::getProviderName(),
            $subject->getIdentifier(),
        );
    }

    public function __construct(string $provider = null, int $identifier = null)
    {
        $this->provider = $provider;
        $this->identifier = $identifier;
    }

    /**
     * Returns whether the subject identity is set.
     */
    public function hasIdentity(): bool
    {
        return !empty($this->provider) && !empty($this->identifier);
    }

    /**
     * Clears the subject identity.
     */
    public function clear(): void
    {
        $this->provider = null;
        $this->identifier = null;
        $this->subject = null;
    }

    /**
     * Returns whether this subject identity equals the given one.
     */
    public function equals(SubjectIdentity $identity): bool
    {
        return $this->provider === $identity->getProvider()
            && $this->identifier === $identity->getIdentifier();
    }

    /**
     * Copy the given subject identity.
     */
    public function copy(SubjectIdentity $identity): void
    {
        if ($this->equals($identity)) {
            return;
        }

        $this->provider = $identity->getProvider();
        $this->identifier = $identity->getIdentifier();
        $this->subject = null;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): SubjectIdentity
    {
        $this->provider = $provider;

        return $this;
    }

    public function getIdentifier(): ?int
    {
        return $this->identifier;
    }

    public function setIdentifier(?int $identifier): SubjectIdentity
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSubject(): ?SubjectInterface
    {
        return $this->subject;
    }

    public function setSubject(?SubjectInterface $subject): SubjectIdentity
    {
        $this->subject = $subject;

        return $this;
    }
}
