<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class ResultCache
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResultCache
{
    /**
     * @var Result[]
     */
    private $results;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Clears the results.
     */
    public function clear()
    {
        $this->results = [];
    }

    /**
     * Adds the result.
     *
     * @param Result $result
     */
    public function addResult(Result $result)
    {

    }

    /**
     * Returns whether a result is registerd for the given subject.
     *
     * @param object $subject
     *
     * @return bool
     */
    public function hasResultForSubject($subject)
    {

    }

    /**
     * Returns the result for the given subject.
     *
     * @param $subject
     *
     * @return Result
     */
    public function getResultForSubject($subject)
    {

    }
}
