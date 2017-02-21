<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    /**
     * @var SubjectIdentity
     */
    protected $subjectIdentity;

    /**
     * @var array
     */
    protected $subjectData = [];


    /**
     * Initializes the subject identity.
     */
    protected function initializeSubjectIdentity()
    {
        $this->subjectIdentity = new SubjectIdentity();
    }

    /**
     * Returns the subjectIdentity.
     *
     * @return SubjectIdentity
     *
     * @internal
     */
    public function getSubjectIdentity()
    {
        return $this->subjectIdentity;
    }

    /**
     * Sets the subject identity (for fixtures usage).
     *
     * @param SubjectIdentity $subjectIdentity
     *
     * @internal
     */
    public function setSubjectIdentity(SubjectIdentity $subjectIdentity)
    {
        $this->subjectIdentity = $subjectIdentity;
    }

    /**
     * Returns whether the relative has subject data or not.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSubjectData($key = null)
    {
        if (!empty($key)) {
            return array_key_exists($key, (array)$this->subjectData) && !empty($this->subjectData[$key]);
        }

        return !empty($this->subjectData);
    }

    /**
     * Returns the subject data, optionally filtered by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSubjectData($key = null)
    {
        if (!empty($key)) {
            if (array_key_exists($key, (array)$this->subjectData)) {
                return $this->subjectData[$key];
            }

            return null;
        }

        return $this->subjectData;
    }

    /**
     * Sets the subject data.
     *
     * @param array|string $keyOrData The key of the data or the whole subject data.
     * @param mixed        $data      The data assigned to the key (must be null if $keyOrData is the whole subject data).
     *
     * @return $this|SubjectRelativeTrait
     */
    public function setSubjectData($keyOrData, $data = null)
    {
        if (is_array($keyOrData) && null === $data) {
            $this->subjectData = $keyOrData;
        } elseif (is_string($keyOrData) && !empty($keyOrData)) {
            if (!is_array($this->subjectData)) {
                $this->subjectData = [];
            }
            $this->subjectData[$keyOrData] = $data;
        } else {
            throw new InvalidArgumentException(sprintf("Bad usage of %s::setSubjectData", static::class));
        }

        return $this;
    }

    /**
     * Unset the subject data by key.
     *
     * @param string $key
     *
     * @return $this|SubjectRelativeTrait
     */
    public function unsetSubjectData($key)
    {
        if (is_string($key) && !empty($key)) {
            if (array_key_exists($key, (array)$this->subjectData)) {
                unset($this->subjectData[$key]);
            }
        } else {
            throw new InvalidArgumentException('Expected key as string.');
        }

        return $this;
    }

    /**
     * Clears the subject (identity and data).
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function clearSubject()
    {
        $this->subjectIdentity->clear();
        $this->subjectData = null;

        return $this;
    }
}
