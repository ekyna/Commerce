<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Trait MarginCacheTrait
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait MarginCacheTrait
{
    /** @var array<string, Margin> */
    private array $cache = [];

    /**
     * Returns the cached margin if any.
     */
    protected function get(string $key): ?Margin
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        return null;
    }

    /**
     * Sets the cached margin.
     */
    protected function set(string $key, Margin $amount): void
    {
        $this->cache[$key] = $amount;
    }
}
