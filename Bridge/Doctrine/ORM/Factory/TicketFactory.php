<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory;

use Ekyna\Component\Commerce\Support\Factory\TicketFactoryInterface;
use Ekyna\Component\Commerce\Support\Factory\TicketMessageFactoryInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class TicketFactory
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TicketFactory extends ResourceFactory implements TicketFactoryInterface
{
    private TicketMessageFactoryInterface $messageFactory;

    public function __construct(TicketMessageFactoryInterface $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function create(): ResourceInterface
    {
        /** @var TicketInterface $ticket */
        $ticket = parent::create();

        $ticket->addMessage(
            $this->messageFactory->create()
        );

        return $ticket;
    }
}
