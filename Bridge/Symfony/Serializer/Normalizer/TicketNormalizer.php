<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class TicketNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketNormalizer extends AbstractResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @inheritDoc
     *
     * @param TicketInterface $ticket
     */
    public function normalize($ticket, $format = null, array $context = [])
    {
        if ($this->contextHasGroup(['Default', 'Ticket'], $context)) {
            $formatter = $this->getFormatter();
            $customer = $ticket->getCustomer();

            $data = [
                'id'           => $ticket->getId(),
                'number'       => $ticket->getNumber(),
                'state'        => $ticket->getState(),
                'subject'      => $ticket->getSubject(),
                // TODO customer, order, quote ?
                'created_at'   => ($date = $ticket->getCreatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_created_at' => ($date = $ticket->getCreatedAt()) ? $formatter->dateTime($date) : null,
                'updated_at'   => ($date = $ticket->getUpdatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_updated_at' => ($date = $ticket->getUpdatedAt()) ? $formatter->dateTime($date) : null,
                'customer' => [
                    'id' => $customer->getId(),
                    'first_name' => $customer->getFirstName(),
                    'last_name' => $customer->getLastName(),
                    'company' => $customer->getCompany(),
                ],
                'orders' => [],
                'quotes' => [],
            ];

            foreach ($ticket->getQuotes() as $quote) {
                $data['quotes'][] = [
                    'id'     => $quote->getId(),
                    'number' => $quote->getNumber(),
                ];
            }

            foreach ($ticket->getOrders() as $order) {
                $data['orders'][] = [
                    'id'     => $order->getId(),
                    'number' => $order->getNumber(),
                ];
            }

            if ($this->contextHasGroup('Ticket', $context)) {
                $data += ['messages' => []];

                foreach ($this->filterMessages($ticket) as $message) {
                    $data['messages'][] = $this->normalizeObject($message, $format, $context);
                }
            }

            return $data;
        }

        return parent::normalize($ticket, $format, $context);
    }

    /**
     * Filters the ticket messages.
     *
     * @param TicketInterface $ticket
     *
     * @return \Ekyna\Component\Commerce\Support\Model\TicketMessageInterface[]
     */
    protected function filterMessages(TicketInterface $ticket)
    {
        return $ticket->getMessages()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TicketInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, TicketInterface::class);
    }
}
