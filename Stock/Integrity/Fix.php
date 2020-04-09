<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

/**
 * Class Fix
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Fix extends Action
{
    /** @var string  */
    private $sql;

    /** @var array  */
    private $parameters;

    /** @var int  */
    private $unitId;

    /**
     * @param string $label
     * @param string $sql
     * @param array  $parameters
     * @param int    $unitId
     */
    public function __construct(string $label, string $sql, array $parameters, int $unitId = null)
    {
        parent::__construct($label);

        $this->sql = $sql;
        $this->parameters = $parameters;
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getUnitId(): ?int
    {
        return $this->unitId;
    }
}
