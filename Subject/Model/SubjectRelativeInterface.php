<?php

namespace Ekyna\Component\Commerce\Subject\Model;

/**
 * Interface SubjectRelativeInterface
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectRelativeInterface
{
    /**
     * Returns whether the relative has subject data or not.
     *
     * @return bool
     */
    public function hasSubjectData();

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
     * @todo reset subject
     */
    public function setSubjectData($keyOrData, $data = null);

    /**
     * Unset the subject data by key.
     *
     * @param string $key
     *
     * @return $this|SubjectRelativeInterface
     * @todo reset subject
     */
    public function unsetSubjectData($key);

    /**
     * Returns the subject (may return null if it has not been resolved yet).
     *
     * @return object|null
     */
    public function getSubject();

    /**
     * Sets the subject.
     *
     * @param object $subject
     *
     * @return object
     */
    public function setSubject($subject);
}
