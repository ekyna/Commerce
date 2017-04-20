<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;

/**
 * Class AdjustmentData
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentData implements AdjustmentDataInterface
{
    private string  $mode;
    private string  $designation;
    private Decimal $amount;
    private ?string $source;
    private bool    $immutable;


    public function __construct(
        string  $mode,
        string  $designation,
        Decimal $amount,
        ?string $source,
        bool    $immutable = true
    ) {
        $this->mode = $mode;
        $this->designation = $designation;
        $this->amount = $amount;
        $this->source = $source;
        $this->immutable = $immutable;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getDesignation(): string
    {
        return $this->designation;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }
}
