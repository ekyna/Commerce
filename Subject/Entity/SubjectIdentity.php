<?php

namespace Ekyna\Component\Commerce\Subject\Entity;

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
     * @var mixed
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
    public function getProvider()
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
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier()
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
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns the subject.
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param mixed $subject
     *
     * @return SubjectIdentity
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }
}
