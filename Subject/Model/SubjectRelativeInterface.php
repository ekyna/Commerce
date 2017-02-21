<?php

namespace Ekyna\Component\Commerce\Subject\Model;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Interface SubjectRelativeInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @see SubjectRelativeTrait
 */
interface SubjectRelativeInterface
{
    /**
     * Returns the subjectIdentity.
     *
     * @return SubjectIdentity
     *
     * @internal
     */
    public function getSubjectIdentity();

    /**
     * Returns whether the relative has subject data or not.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSubjectData($key = null);

    /**
     * Returns the subject data, optionally filtered by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSubjectData($key = null);

    /**
     * Sets the subject data.
     *
     * @param array|string $keyOrData The key of the data or the whole subject data.
     * @param mixed        $data      The data assigned to the key (must be null if $keyOrData is the whole subject data).
     *
     * @return $this|SubjectRelativeInterface
     */
    public function setSubjectData($keyOrData, $data = null);

    /**
     * Unset the subject data by key.
     *
     * @param string $key
     *
     * @return $this|SubjectRelativeInterface
     */
    public function unsetSubjectData($key);

    /**
     * Clears the subject (identity and data).
     *
     * @return $this|SubjectRelativeInterface
     *
     * @internal
     */
    public function clearSubject();
}
