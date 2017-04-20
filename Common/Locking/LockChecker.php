<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Locking;

use DateTime;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Throwable;

/**
 * Class LockChecker
 * @package Ekyna\Component\Commerce\Common\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LockChecker
{
    private DateTime $lockSince;
    private DateTime $lockStart;
    private DateTime $lockEnd;
    private DateTime $today;
    /** @var array<LockResolverInterface> */
    private array $resolvers;
    private bool  $enabled = true;

    /**
     * Creates the date base on the input parts.
     *
     * Example input: '<em>midnight first day of -2 month;+10 days</em>'.<br>
     * This will create the date with '<em>midnight first day of -2 month</em>',
     * then will apply each subsequent part ('<em>+10 days</em>', etc) using
     * <strong>modify()</strong> method.
     *
     * @throws LogicException
     */
    public static function createDate(string $input, string $property): DateTime
    {
        $parts = explode(';', $input);

        try {
            $date = new DateTime($parts[0]);
            foreach (array_slice($parts, 1) as $part) {
                $date->modify($part);
            }
        } catch (Throwable $e) {
            throw new LogicException("Failed to parse '$property' date ($input).");
        }

        return $date;
    }

    /**
     * @param array<LockResolverInterface> $resolvers
     */
    public function __construct(array $resolvers, string $start, string $end, string $since)
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }

        $this->lockStart = self::createDate($start, 'lock start')->setTime(0, 0);
        $this->lockEnd = self::createDate($end, 'lock end')->setTime(23, 59, 59, 999999);
        $this->lockSince = self::createDate($since, 'lock since')->setTime(0, 0);
        $this->today = new DateTime();
    }

    /**
     * @throws LogicException
     */
    public function addResolver(LockResolverInterface $resolver): void
    {
        $class = get_class($resolver);

        if (isset($this->resolvers[$class])) {
            throw new LogicException("Lock resolver $class is already registered.");
        }

        $this->resolvers[$class] = $resolver;
    }

    /**
     * Returns whether the given resource is locked.
     *
     * <code>
     *         Start                       End             Resource's
     * ----------|--------------------------|----------->   resolved
     *   Always  |  Locked if current date  |    Not          date
     *   locked  |  is lower that 'since'   |  locked
     * </code>
     */
    public function isLocked(ResourceInterface $resource): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (null === $date = $this->resolveDate($resource)) {
            return false;
        }

        if ($date < $this->lockStart) {
            return true;
        }

        if ($this->today < $this->lockSince) {
            return false;
        }

        if ($date < $this->lockEnd) {
            return true;
        }

        return false;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Resolves the resource date to check.
     */
    private function resolveDate(ResourceInterface $resource): ?DateTime
    {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->support($resource)) {
                continue;
            }

            return $resolver->resolve($resource);
        }

        return null;
    }
}
