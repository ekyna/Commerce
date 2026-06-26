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
    /** @var array<string, Action> */
    private array $actions = [];
    /** @var array<string, Comment> */
    private array $comments = [];
    /** @var array<string, string> */
    private array $classes = [];
    public ?Icon  $icon     = null;

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
            throw new InvalidArgumentException("Action '$name' does not exist.");
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
            throw new InvalidArgumentException("Action '$name' does not exist.");
        }

        return $this->actions[$name];
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function addComment(string $name, Comment $comment, bool $replace = false): void
    {
        if ($this->hasComment($name) && !$replace) {
            throw new InvalidArgumentException("Comment '$name' already exists.");
        }

        $this->comments[$name] = $comment;
    }

    public function removeComment(string $name): void
    {
        if (!$this->hasComment($name)) {
            throw new InvalidArgumentException("Comment '$name' does not exist.");
        }

        unset($this->comments[$name]);
    }

    public function hasComment(string $name): bool
    {
        return isset($this->comments[$name]);
    }

    public function getComment(string $name): Comment
    {
        if (!$this->hasComment($name)) {
            throw new InvalidArgumentException("Comment '$name' does not exist.");
        }

        return $this->comments[$name];
    }

    /**
     * @return array<string, Comment>
     */
    public function getComments(): array
    {
        return $this->comments;
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

    public function setClass(string $cell, string $class): void
    {
        $this->classes[$cell] = $class;
    }

    public function getClass(string $column): string
    {
        return $this->classes[$column] ?? '';
    }
}
