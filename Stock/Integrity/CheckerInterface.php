<?php

namespace Ekyna\Component\Commerce\Stock\Integrity;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface CheckerInterface
 * @package Ekyna\Component\Commerce\Stock\Integrity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface CheckerInterface
{
    /**
     * Sets the connection.
     *
     * @param Connection $connection
     */
    public function setConnection(Connection $connection): void;

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Returns the actions.
     *
     * @return Action[]
     */
    public function getActions(): array;

    /**
     * Checks the integrity.
     *
     * @param OutputInterface $output
     *
     * @return bool Whether problems are detected.
     */
    public function check(OutputInterface $output): bool;

    /**
     * Displays the results.
     *
     * @param OutputInterface $output
     */
    public function display(OutputInterface $output): void;

    /**
     * Builds the actions.
     *
     * @param OutputInterface $output
     */
    public function build(OutputInterface $output): void;

    /**
     * Applies the actions.
     *
     * @param OutputInterface $output
     * @param array           $unitIds The changed stock units identifiers.
     */
    public function fix(OutputInterface $output, array &$unitIds): void;
}
