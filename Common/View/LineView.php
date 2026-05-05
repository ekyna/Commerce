<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class LineView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LineView extends AbstractView
{
    public ?string $designation    = null;
    public ?string $description    = null;
    public ?string $reference      = null;
    public ?string $availability   = null;
    public ?string $unit           = null; // The unit price.
    public ?string $quantity       = null;
    public ?string $gross          = null; // Total price before applying discounts and taxes.
    public ?string $discountRates  = null;
    public ?string $discountAmount = null;
    public ?string $base           = null; // Total price after applying discounts and before applying taxes.
    public ?string $includes       = null;
    public ?string $taxRates       = null;
    public ?string $taxAmount      = null;
    public ?string $total          = null; // Total price after applying discounts and taxes.
    public ?string $margin         = null; // The margin in percentage.
    public ?string $cost           = null; // The unit cost.
    public ?string $weight         = null; // The weight
    public ?string $hsCode         = null; // The HS code.
    public ?string $ean13          = null; // The EAN13 code.
    public ?string $mpn            = null; // The manufacturer product number.
    public bool    $private        = false;
    public bool    $batchable      = false;
    public bool    $gift           = false; // TODO To get rid of 'Offert' value...
    public ?object $source         = null;
    /** @var array<LineView> */
    private array $lines = [];
    /** @var array<Action> */
    private array $actions = [];

    public function __construct(
        public string $id,
        public string $formId,
        public int    $number,
        public int    $level = 0
    ) {
    }

    public function addAction(string $name, Action $action, bool $replace = false): void
    {
        if ($this->hasAction($name) && !$replace) {
            throw new InvalidArgumentException("Action '$name' already exists.");
        }

        $this->actions[$name] = $action;
    }

    public function removeAction(string $name): void
    {
        if (!$this->hasAction($name)) {
            throw new InvalidArgumentException("Action '$name' already exists.");
        }

        unset($this->actions[$name]);
    }

    public function hasAction(string $name): bool
    {
        return isset($this->actions[$name]);
    }

    public function getAction(string $name): Action
    {
        if (!$this->hasAction($name)) {
            throw new InvalidArgumentException("Action '$name' already exists.");
        }

        return $this->actions[$name];
    }

    /**
     * @return array<Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function addLine(LineView $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * @return array<LineView>
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}
