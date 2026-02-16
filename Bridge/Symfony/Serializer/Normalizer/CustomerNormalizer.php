<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class CustomerNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param CustomerInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if ($format === 'csv' && self::contextHasGroup('TableExport', $context)) {
            return (string)$object;
        }

        $data = parent::normalize($object, $format, $context);

        $parent = $object->getParent();

        if (self::contextHasGroup(['Search'], $context)) {
            return array_replace($data, [
                'number'        => $object->getNumber(),
                'company'       => $object->getCompany(),
                'companyNumber' => $object->getCompanyNumber(),
                'email'         => $object->getEmail(),
                'firstName'     => $object->getFirstName(),
                'lastName'      => $object->getLastName(),
                'parent'        => $parent?->getId(),
                'currency'      => $object->getCurrency()->getCode(),
                'locale'        => $object->getLocale(),
            ]);
        }

        if (self::contextHasGroup(['Default', 'Customer', 'Summary'], $context)) {
            $data = array_replace($data, [
                'number'         => $object->getNumber(),
                'company'        => $object->getCompany(),
                'company_number' => $object->getCompanyNumber(),
                'email'          => $object->getEmail(),
                'first_name'     => $object->getFirstName(),
                'last_name'      => $object->getLastName(),
                'parent'         => $parent ? $parent->getId() : null,
                'currency'       => $object->getCurrency()->getCode(),
                'locale'         => $object->getLocale(),
                'phone'          => $this->normalizeObject($object->getPhone(), $format, $context),
                'mobile'         => $this->normalizeObject($object->getMobile(), $format, $context),
            ]);
        }

        if (self::contextHasGroup('Summary', $context)) {
            $payment = $parent ?: $object;

            $data = array_replace($data, [
                'group'                  => (string)$object->getCustomerGroup(),
                'parent'                 => (string)$parent,
                'vat_number'             => $payment->getVatNumber(),
                'vat_valid'              => $payment->isVatValid(),
                'payment_term'           => (string)$payment->getPaymentTerm(),
                'outstanding_limit'      => $payment->getOutstandingLimit()->toFixed(5),
                'outstanding_balance'    => $payment->getOutstandingBalance()->toFixed(5),
                'outstanding_overflow'   => $payment->isOutstandingOverflow(),
                'credit_balance'         => $payment->getCreditBalance()->toFixed(5),
                'default_payment_method' => (string)$payment->getDefaultPaymentMethod(),
                'payment_methods'        => implode(', ', array_map(function (PaymentMethodInterface $method) {
                    return (string)$method;
                }, $payment->getPaymentMethods()->toArray())),
                'description'            => $payment->getDescription(),
            ]);
        }

        return $data;
    }
}
