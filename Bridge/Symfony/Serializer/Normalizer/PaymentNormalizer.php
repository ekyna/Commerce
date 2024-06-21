<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

use function array_replace;

/**
 * Class PaymentNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param PaymentInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(['Default', 'OrderPayment'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'       => $object->getNumber(),
                'company'      => $sale->getCompany(),
                'email'        => $sale->getEmail(),
                'first_name'   => $sale->getFirstName(),
                'last_name'    => $sale->getLastName(),
                'method'       => $object->getMethod()->getName(),
                'state'        => $object->getState(),
                'currency'     => $currency = $object->getCurrency()->getCode(),
                'amount'       => $object->getAmount()->toFixed(Money::getPrecision($currency)),
                'completed_at' => $object->getCompletedAt()?->format('Y-m-d'),
            ]);
        } elseif (self::contextHasGroup(['Search'], $context)) {
            $sale = $object->getSale();

            $data = array_replace($data, [
                'number'      => $object->getNumber(),
                'sale_number' => $sale->getNumber(),
                'sale_id'     => $sale->getId(),
            ]);
        }

        return $data;
    }
}
