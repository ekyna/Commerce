<?php

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
    /**
     * @var DateTime
     */
    private $lockSince;

    /**
     * @var DateTime
     */
    private $lockStart;

    /**
     * @var DateTime
     */
    private $lockEnd;

    /**
     * @var DateTime
     */
    private $today;

    /**
     * @var LockResolverInterface[]
     */
    private $resolvers;


    /**
     * Creates the date base on the input parts.
     *
     * Example input: '<em>midnight first day of -2 month;+10 days</em>'.<br>
     * This will create the date with '<em>midnight first day of -2 month</em>',
     * then will apply each subsequent part ('<em>+10 days</em>', etc) using
     * <strong>modify()</strong> method.
     *
     * @param string $input
     * @param string $property
     *
     * @return DateTime
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
     * Constructor.
     *
     * @param LockResolverInterface[] $resolvers
     * @param string                  $start
     * @param string                  $end
     * @param string                  $since
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
     * Adds the resolver.
     *
     * @param LockResolverInterface $resolver
     *
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
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function isLocked(ResourceInterface $resource): bool
    {
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

    /**
     * Resolves the resource date to check.
     *
     * @param ResourceInterface $resource
     *
     * @return DateTime|null
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
