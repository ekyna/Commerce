<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareInterface;
use Ekyna\Component\Resource\Helper\ResourceHelperAwareTrait;

use function array_map;
use function array_replace;
use function implode;

/**
 * Class CustomerNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerNormalizer extends ResourceNormalizer implements ResourceHelperAwareInterface
{
    use ResourceHelperAwareTrait;

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

        if (self::contextHasGroup(['Summary'], $context)) {
            return $this->normalizeForSummary($object, $format, $context);
        }

        if (self::contextHasGroup(['Api'], $context)) {
            return $this->normalizeForApi($object, $format, $context);
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

        if (self::contextHasGroup(['Default', 'Customer'], $context)) {
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

        return $data;
    }

    private function normalizeForSummary(CustomerInterface $customer, string $format, array $context): array
    {
        $parent = $customer->getParent();
        $payment = $parent ?: $customer;

        return [
            'id'                     => $customer->getId(),
            'number'                 => $customer->getNumber(),
            'company'                => $customer->getCompany(),
            'company_number'         => $customer->getCompanyNumber(),
            'email'                  => $customer->getEmail(),
            'first_name'             => $customer->getFirstName(),
            'last_name'              => $customer->getLastName(),
            'parent'                 => $parent ? (string)$parent : null,
            'currency'               => $customer->getCurrency()->getCode(),
            'locale'                 => $customer->getLocale(),
            'phone'                  => $this->normalizeObject($customer->getPhone(), $format, $context),
            'mobile'                 => $this->normalizeObject($customer->getMobile(), $format, $context),
            'group'                  => (string)$customer->getCustomerGroup(),
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
        ];
    }

    private function normalizeForApi(CustomerInterface $customer, string $format, array $context): array
    {
        $data = $this->normalizeForSummary($customer, $format, $context);

        $parent = $customer->getParent();
        $group = $customer->getCustomerGroup();
        $payment = $parent ?: $customer;
        $term = $payment->getPaymentTerm();

        return array_replace($data, [
            '_links'          => [
                'self' => [
                    'href' => $this->getResourceHelper()->generateResourcePath($customer, 'api_read', [], true),
                ],
            ],
            'parent'          => $parent ? [
                'id'             => $parent->getId(),
                'number'         => $parent->getNumber(),
                'company'        => $parent->getCompany(),
                'company_number' => $parent->getCompanyNumber(),
                'email'          => $parent->getEmail(),
                'first_name'     => $parent->getFirstName(),
                'last_name'      => $parent->getLastName(),
                '_links'         => [
                    'self' => [
                        'href' => $this->getResourceHelper()->generateResourcePath($parent, 'api_read', [], true),
                    ],
                ],
            ] : null,
            'group'           => [
                'id'   => $group->getId(),
                'name' => $group->getName(),
            ],
            'payment_term'    => $term ? [
                'id'   => $term->getId(),
                'name' => $term->getName(),
            ] : null,
            'payment_methods' => array_map(function (PaymentMethodInterface $method) {
                return [
                    'id'   => $method->getId(),
                    'name' => $method->getName(),
                ];
            }, $payment->getPaymentMethods()->toArray()),
        ]);
    }
}
