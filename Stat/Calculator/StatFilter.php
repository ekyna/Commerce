<?php

namespace Ekyna\Component\Commerce\Stat\Calculator;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;

/**
 * Class StatFilter
 * @package Ekyna\Component\Commerce\Stat\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatFilter
{
    // TODO private $customerGroups

    /**
     * @var string[]
     */
    private $countries = [];

    /**
     * @var bool
     */
    private $excludeCountries = false;

    /**
     * @var array [string => int[]]
     */
    private $subjects = [];

    /**
     * @var bool
     */
    private $excludeSubjects = false;


    /**
     * Returns the countries codes.
     *
     * @return string[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * Sets the countries codes.
     *
     * @param string[] $codes
     *
     * @return StatFilter
     */
    public function setCountries(array $codes): self
    {
        $this->countries = [];

        foreach ($codes as $code) {
            $this->addCountry($code);
        }

        return $this;
    }

    /**
     * Adds the country code.
     *
     * @param string $code
     *
     * @return StatFilter
     */
    public function addCountry(string $code): self
    {
        $this->countries[] = strtoupper($code);

        return $this;
    }

    /**
     * Returns whether to exclude countries.
     *
     * @return bool
     */
    public function isExcludeCountries(): bool
    {
        return $this->excludeCountries;
    }

    /**
     * Sets whether to exclude countries.
     *
     * @param bool $exclude
     *
     * @return StatFilter
     */
    public function setExcludeCountries(bool $exclude): self
    {
        $this->excludeCountries = $exclude;

        return $this;
    }

    /**
     * Returns the subjects.
     *
     * @return array [string => int[]]
     */
    public function getSubjects(): array
    {
        return $this->subjects;
    }

    /**
     * Sets the subjects.
     *
     * @param array $subjects [string => int[]]
     *
     * @return StatFilter
     */
    public function setSubjects(array $subjects): StatFilter
    {
        $this->subjects = [];

        foreach ($subjects as $provider => $ids) {
            foreach ($ids as $id) {
                $this->addSubject($provider, $id);
            }
        }

        return $this;
    }

    /**
     * Adds the subject id.
     *
     * @param string $provider
     * @param int    $id
     *
     * @return StatFilter
     */
    public function addSubject(string $provider, int $id): self
    {
        if (!isset($this->subjects[$provider])) {
            $this->subjects[$provider] = [];
        }

        $this->subjects[$provider][] = $id;

        return $this;
    }

    /**
     * Returns whether the given subject identity is filtered.
     *
     * @param SubjectIdentity $subject
     *
     * @return bool
     */
    public function hasSubject(SubjectIdentity $subject): bool
    {
        if (!isset($this->subjects[$subject->getProvider()])) {
            return false;
        }

        return in_array($subject->getIdentifier(), $this->subjects[$subject->getProvider()]);
    }

    /**
     * Returns whether to exclude subjects.
     *
     * @return bool
     */
    public function isExcludeSubjects(): bool
    {
        return $this->excludeSubjects;
    }

    /**
     * Sets whether to exclude subjects.
     *
     * @param bool $excludeSubjects
     *
     * @return StatFilter
     */
    public function setExcludeSubjects(bool $excludeSubjects): StatFilter
    {
        $this->excludeSubjects = $excludeSubjects;

        return $this;
    }

    /**
     * Returns whether the filter is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->countries) && empty($this->subjects);
    }
}
