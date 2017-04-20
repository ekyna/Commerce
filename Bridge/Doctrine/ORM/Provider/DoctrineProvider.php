<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Statement;
use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class DoctrineProvider
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DoctrineProvider extends AbstractExchangeRateProvider
{
    private Connection $connection;

    private ?Statement $find   = null;
    private ?Statement $create = null;


    public function __construct(Connection $connection, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->connection = $connection;
    }

    protected function fetch(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        if (null !== $rate = $this->find($base, $quote, $date)) {
            return $rate;
        }

        if (null !== $rate = $this->find($quote, $base, $date)) {
            return (new Decimal(1))->div($rate);
        }

        return null;
    }

    protected function persist(string $base, string $quote, DateTimeInterface $date, Decimal $rate): void
    {
        if (!$this->create) {
            /** @noinspection SqlDialectInspection */
            $this->create = $this->connection->prepare(
                'INSERT INTO commerce_exchange_rate(base, quote, date, rate) ' .
                'VALUES (:base, :quote, :date, :rate)'
            );
        }

        try {
            $result = $this->create->executeStatement([
                'base'  => $base,
                'quote' => $quote,
                'date'  => $date->format('Y-m-d H:i:s'),
                'rate'  => $rate,
            ]);
        } catch (Exception $exception) {
            throw new RuntimeException('Failed to persist exchange rate.', 0, $exception);
        }

        if (0 === $result) {
            throw new RuntimeException('Failed to persist exchange rate.');
        }
    }

    /**
     * Finds the exchange rate.
     */
    private function find(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        if (!$this->find) {
            /** @noinspection SqlDialectInspection */
            $this->find = $this->connection->prepare(
                'SELECT e.rate FROM commerce_exchange_rate e ' .
                'WHERE e.base=:base AND e.quote=:quote AND e.date=:date ' .
                'LIMIT 1'
            );
        }

        try {
            $result = $this->find->executeQuery([
                'base'  => $base,
                'quote' => $quote,
                'date'  => $date->format('Y-m-d H:i:s'),
            ]);
        } catch (Exception $exception) {
            throw new RuntimeException('Failed to fetch exchange rate.', 0, $exception);
        }

        if (false !== $rate = $result->fetchOne()) {
            return new Decimal($rate);
        }

        return null;
    }
}
