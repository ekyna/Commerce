<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Install;

use Ekyna\Component\Commerce\Install\Logger\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class ConsoleLogger
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Install
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConsoleLogger implements LoggerInterface
{
    private OutputInterface $output;


    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function resource(string $name, string $state): void
    {
        $this->output->writeln(sprintf(
            '- <comment>%s</comment> %s %s.',
            $name,
            str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT),
            $state
        ));
    }
}
