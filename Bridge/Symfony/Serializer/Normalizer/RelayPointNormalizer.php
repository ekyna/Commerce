<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Serializer\AbstractResourceNormalizer;

/**
 * Class RelayPointNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointNormalizer extends AbstractResourceNormalizer
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function normalize($relayPoint, $format = null, array $context = [])
    {
        /** @var RelayPointInterface $relayPoint */
        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('Default', $groups)) {
            $data = [
                'id'          => $relayPoint->getId(),
                'number'      => $relayPoint->getNumber(),
                'platform'    => $relayPoint->getPlatform(),
                'company'     => $relayPoint->getCompany(),
                //'gender'      => $relayPoint->getGender(),
                //'first_name'  => $relayPoint->getFirstName(),
                //'last_name'   => $relayPoint->getLastName(),
                'street'      => $relayPoint->getStreet(),
                'complement'  => $relayPoint->getComplement(),
                'supplement'  => $relayPoint->getSupplement(),
                'postal_code' => $relayPoint->getPostalCode(),
                'city'        => $relayPoint->getCity(),
                'country'     => $relayPoint->getCountry()->getName(),
                //'state'        => $address->getCity(),

                'phone'  => $this->normalizeObject($relayPoint->getPhone(), $format, $context),
                'mobile' => $this->normalizeObject($relayPoint->getMobile(), $format, $context),

                'distance'  => $relayPoint->getDistance(),
                'longitude' => $relayPoint->getLongitude(),
                'latitude'  => $relayPoint->getLatitude(),
            ];

            foreach ($relayPoint->getOpeningHours() as $oh) {
                $data['opening_hours'][] = [
                    'day'    => $oh->getDay(),
                    'label'  => $this->localizedDayOfWeek($oh->getDay()),
                    'ranges' => $oh->getRanges(),
                ];
            }

            return $data;
        }

        return parent::normalize($relayPoint, $format, $context);
    }

    /**
     * Returns the localized day of week.
     *
     * @param int $dayOfWeek
     *
     * @return string
     */
    protected function localizedDayOfWeek($dayOfWeek)
    {
        if (class_exists('\IntlDateFormatter')) {
            $date = new \DateTime('2017-01-01'); // Starts sunday
            $date->modify('+' . $dayOfWeek . ' days');

            $formatter = \IntlDateFormatter::create(
                $this->localeProvider->getCurrentLocale(),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                $date->getTimezone(),
                null,
                'eeee'
            );

            return $formatter->format($date->getTimestamp());
        }

        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ][$dayOfWeek];
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
        return $data instanceof RelayPointInterface;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && is_subclass_of($type, RelayPointInterface::class);
    }
}
