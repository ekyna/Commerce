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
     * Returns the provider.
     *
     * @return string
     *
     * @internal
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
     *
     * @internal
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
     *
     * @internal
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
     *
     * @internal
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
     *
     * @internal
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
     *
     * @internal
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }
}
