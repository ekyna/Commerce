<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class TicketNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketNormalizer extends ResourceNormalizer
{
    use FormatterAwareTrait;

    /**
     * @inheritDoc
     *
     * @param TicketInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (self::contextHasGroup(['Default', 'Ticket'], $context)) {
            $formatter = $this->getFormatter();

            $data = [
                'id'           => $object->getId(),
                'number'       => $object->getNumber(),
                'state'        => $object->getState(),
                'internal'     => $object->isInternal(),
                'subject'      => $object->getSubject(),
                'created_at'   => ($date = $object->getCreatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_created_at' => ($date = $object->getCreatedAt()) ? $formatter->dateTime($date) : null,
                'updated_at'   => ($date = $object->getUpdatedAt()) ? $date->format('Y-m-d H:i:s') : null,
                'f_updated_at' => ($date = $object->getUpdatedAt()) ? $formatter->dateTime($date) : null,
                'customer'     => null,
                'orders'       => [],
                'quotes'       => [],
            ];

            if ($customer = $object->getCustomer()) {
                $data['customer'] = [
                    'id'         => $customer->getId(),
                    'first_name' => $customer->getFirstName(),
                    'last_name'  => $customer->getLastName(),
                    'company'    => $customer->getCompany(),
                ];
            }

            foreach ($object->getQuotes() as $quote) {
                $data['quotes'][] = [
                    'id'     => $quote->getId(),
                    'number' => $quote->getNumber(),
                ];
            }

            foreach ($object->getOrders() as $order) {
                $data['orders'][] = [
                    'id'     => $order->getId(),
                    'number' => $order->getNumber(),
                ];
            }

            if (self::contextHasGroup('Ticket', $context)) {
                $data += ['messages' => []];

                foreach ($this->filterMessages($object) as $message) {
                    $data['messages'][] = $this->normalizeObject($message, $format, $context);
                }
            }

            return $data;
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * Filters the ticket messages.
     *
     * @return array<TicketMessageInterface>
     */
    protected function filterMessages(TicketInterface $ticket): array
    {
        return $ticket->getMessages()->toArray();
    }
}
