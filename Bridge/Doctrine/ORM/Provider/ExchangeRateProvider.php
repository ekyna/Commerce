<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class ExchangeRateProvider
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRateProvider extends AbstractExchangeRateProvider
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Statement
     */
    private $find;

    /**
     * @var Statement
     */
    private $create;


    /**
     * Constructor.
     *
     * @param Connection                         $connection
     * @param ExchangeRateProviderInterface|null $fallback
     */
    public function __construct(Connection $connection, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    protected function fetch(string $base, string $quote, \DateTime $date): ?float
    {
        if (null !== $rate = $this->find($base, $quote, $date)) {
            return (float)$rate;
        }

        if (null !== $rate = $this->find($quote, $base, $date)) {
            return 1 / (float)$rate;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function persist(string $base, string $quote, \DateTime $date, float $rate): void
    {
        if (!$this->create) {
            $this->create = $this->connection->prepare(
                'INSERT INTO commerce_exchange_rate(base, quote, date, rate) ' .
                'VALUES (:base, :quote, :date, :rate)'
            );
        }

        $result = $this->create->execute([
            'base'  => $base,
            'quote' => $quote,
            'date'  => $date->format('Y-m-d H:i:s'),
            'rate'  => $rate,
        ]);

        if (!$result) {
            throw new RuntimeException("Failed to create exchange rate.");
        }
    }

    /**
     * Finds the exchange rate.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     *
     * @return float|null
     */
    private function find(string $base, string $quote, \DateTime $date): ?float
    {
        if (!$this->find) {
            $this->find = $this->connection->prepare(
                'SELECT e.rate FROM commerce_exchange_rate e ' .
                'WHERE e.base=:base AND e.quote=:quote AND e.date=:date ' .
                'LIMIT 1'
            );
        }

        $result = $this->find->execute([
            'base'  => $base,
            'quote' => $quote,
            'date'  => $date->format('Y-m-d H:i:s'),
        ]);

        if (!$result) {
            throw new RuntimeException("Failed to fetch exchange rate.");
        }

        if (false !== $rate = $this->find->fetchColumn(0)) {
            return (float)$rate;
        }

        return null;
    }
}
