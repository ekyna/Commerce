<?php

namespace Ekyna\Component\Commerce\Subject\Entity;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectIdentity
 * @package Ekyna\Component\Commerce\Subject\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SubjectIdentity
{
    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var SubjectInterface
     */
    private $subject;


    /**
     * Returns whether or not the subject identity is set.
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return !empty($this->provider) && !empty($this->identifier);
    }

    /**
     * Clears the subject identity.
     */
    public function clear()
    {
        $this->provider = null;
        $this->identifier = null;
        $this->subject = null;
    }

    /**
     * Returns whether or not this subject identity equals the given one.
     *
     * @param SubjectIdentity $identity
     *
     * @return bool
     */
    public function equals(SubjectIdentity $identity)
    {
        return $this->provider === $identity->getProvider()
            && $this->identifier === $identity->getIdentifier();
    }

    /**
     * Copy the given subject identity.
     *
     * @param SubjectIdentity $identity
     */
    public function copy(SubjectIdentity $identity)
    {
        $this->provider = $identity->getProvider();
        $this->identifier = $identity->getIdentifier();
    }

    /**
     * Returns the provider.
     *
     * @return string
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * Sets the provider.
     *
     * @param string $provider
     *
     * @return SubjectIdentity
     */
    public function setProvider(string $provider = null): SubjectIdentity
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Sets the identifier.
     *
     * @param string $identifier
     *
     * @return SubjectIdentity
     */
    public function setIdentifier(string $identifier = null): SubjectIdentity
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns the subject.
     *
     * @return SubjectInterface
     */
    public function getSubject(): ?SubjectInterface
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param SubjectInterface $subject
     *
     * @return SubjectIdentity
     */
    public function setSubject(SubjectInterface $subject = null): SubjectIdentity
    {
        $this->subject = $subject;

        return $this;
    }
}
