<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractChecker
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractChecker implements CheckerInterface
{
    /** @var Connection */
    protected $connection;

    /** @var array */
    protected $results;

    /** @var Action[] */
    protected $actions;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->results = [];
        $this->actions = [];
    }

    /**
     * Sets the connection.
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @inheritDoc
     */
    public function check(OutputInterface $output): bool
    {
        $select = $this->connection->executeQuery($this->getSql());

        while (false !== $result = $select->fetch(\PDO::FETCH_ASSOC)) {
            $this->normalize($result);

            if ($this->filter($result)) {
                continue;
            }

            $this->results[] = $result;
        }

        return !empty($this->results);
    }

    /**
     * @inheritDoc
     */
    public function display(OutputInterface $output): void
    {
        $map = $this->getMap();

        $table = new Table($output);
        $table->setHeaders(array_values($map));
        $keys = array_keys($map);
        foreach ($this->results as $result) {
            $data = [];
            foreach ($keys as $key) {
                $data[] = $result[$key];
            }
            $table->addRow($data);
        }

        $table->render();
    }

    /**
     * @inheritDoc
     */
    public function build(OutputInterface $output): void
    {

    }

    /**
     * @inheritDoc
     */
    public function fix(OutputInterface $output, array &$unitIds): void
    {
        if (empty($this->actions)) {
            return;
        }

        foreach ($this->actions as $action) {
            if (!$action instanceof Fix) {
                continue;
            }

            $output->write($action->getLabel() . ': ');

            if ($this->connection->executeUpdate($action->getSql(), $action->getParameters())) {
                $output->writeln('<info>ok</info>');

                $id = $action->getUnitId();
                if (0 < $id && !in_array($id, $unitIds, true)) {
                    $unitIds[] = $id;
                }
            } else {
                $output->writeln('<info>error</info>');
                throw new \Exception("Failed to apply fix");
            }
        }
    }

    /**
     * Returns the sql.
     *
     * @return string
     */
    protected function getSql(): string
    {
        return "";
    }

    /**
     * Normalizes the result.
     *
     * @param array $result
     */
    protected function normalize(array &$result): void
    {

    }

    /**
     * Filters the result.
     *
     * @param array $result
     *
     * @return bool Whether to skip this result.
     */
    protected function filter(array $result): bool
    {
        return false;
    }

    /**
     * returns the display map.
     *
     * @return array
     */
    protected function getMap(): array
    {
        return [];
    }
}
