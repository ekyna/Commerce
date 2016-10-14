<?php

namespace Ekyna\Component\Commerce\Subject\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Trait SubjectRelativeTrait
 * @package Ekyna\Component\Commerce\Subject\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @see SubjectRelativeInterface
 */
trait SubjectRelativeTrait
{
    /**
     * @var array
     */
    protected $subjectData = [];

    /**
     * @var mixed
     */
    protected $subject;


    /**
     * @inheritdoc
     */
    public function hasSubjectData()
    {
        return !empty($this->subjectData);
    }

    /**
     * @inheritdoc
     */
    public function getSubjectData($key = null)
    {
        if (0 < strlen($key)) {
            if (array_key_exists($key, (array)$this->subjectData)) {
                return $this->subjectData[$key];
            }

            return null;
        }

        return $this->subjectData;
    }

    /**
     * @inheritdoc
     */
    public function setSubjectData($keyOrData, $data = null)
    {
        if (is_array($keyOrData) && null === $data) {
            $this->subjectData = $keyOrData;
        } elseif (is_string($keyOrData) && 0 < strlen($keyOrData)) {
            $this->subjectData[$keyOrData] = $data;
        } else {
            throw new InvalidArgumentException(sprintf("Bad usage of %s::setSubjectData", static::class));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unsetSubjectData($key)
    {
        if (is_string($key) && 0 < strlen($key)) {
            if (array_key_exists($key, $this->subjectData)) {
                unset($this->subjectData[$key]);
            }
        } else {
            throw new InvalidArgumentException('Expected key as string.');
        }

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
     * @return mixed
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }
}
