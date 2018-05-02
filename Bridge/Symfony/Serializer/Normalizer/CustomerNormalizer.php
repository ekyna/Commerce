<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class CustomerNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerNormalizer extends AbstractResourceNormalizer
{
    /**
     * @inheritdoc
     *
     * @param CustomerInterface $customer
     */
    public function normalize($customer, $format = null, array $context = [])
    {
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if ($format === 'csv' && in_array('TableExport', $groups)) {
            return (string)$customer;
        }

        $data = parent::normalize($customer, $format, $context);

        $parent = $customer->getParent();

        if (0 < count(array_intersect(['Default', 'Search', 'Summary'], $groups))) {
            //if (in_array('Default', $groups) || in_array('Search', $groups)) {
            $data = array_replace($data, [
                'number'     => $customer->getNumber(),
                'company'    => $customer->getCompany(),
                'email'      => $customer->getEmail(),
                'first_name' => $customer->getFirstName(),
                'last_name'  => $customer->getLastName(),
                'phone'      => $this->normalizeObject($customer->getPhone(), $format, $context),
                'mobile'     => $this->normalizeObject($customer->getMobile(), $format, $context),
                'parent'     => $parent ? $parent->getId() : null,
            ]);
        }

        if (in_array('Summary', $groups)) {
            $payment = $parent ? $parent : $customer;

            $data = array_replace($data, [
                'group'               => (string)$customer->getCustomerGroup(),
                'parent'              => (string)$parent,
                'vat_number'          => $payment->getVatNumber(),
                'vat_valid'           => $payment->isVatValid(),
                'payment_term'        => (string)$payment->getPaymentTerm(),
                'outstanding_limit'   => $payment->getOutstandingLimit(),
                'outstanding_balance' => $payment->getOutstandingBalance(),
                'credit_balance'      => $payment->getCreditBalance(),
                'description'         => $payment->getDescription(),
            ]);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //$object = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CustomerInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, CustomerInterface::class);
    }
}
