<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use DateTime;
use Ekyna\Component\Commerce\Shipment\Model\RelayPointInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use IntlDateFormatter;

/**
 * Class RelayPointNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RelayPointNormalizer extends ResourceNormalizer
{
    private LocaleProviderInterface $localeProvider;


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
     *
     * @param RelayPointInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        if (self::contextHasGroup(['Default', 'RelayPoint'], $context)) {
            $data = [
                'id'          => $object->getId(),
                'number'      => $object->getNumber(),
                'platform'    => $object->getPlatformName(),
                'company'     => $object->getCompany(),
                //'gender'      => $relayPoint->getGender(),
                //'first_name'  => $relayPoint->getFirstName(),
                //'last_name'   => $relayPoint->getLastName(),
                'street'      => $object->getStreet(),
                'complement'  => $object->getComplement(),
                'supplement'  => $object->getSupplement(),
                'postal_code' => $object->getPostalCode(),
                'city'        => $object->getCity(),
                'country'     => $object->getCountry()->getName(),
                //'state'        => $address->getCity(),

                'phone'  => $this->normalizeObject($object->getPhone(), $format, $context),
                'mobile' => $this->normalizeObject($object->getMobile(), $format, $context),

                'distance'  => $object->getDistance(),
                'longitude' => $object->getLongitude(),
                'latitude'  => $object->getLatitude(),
            ];

            foreach ($object->getOpeningHours() as $oh) {
                $data['opening_hours'][] = [
                    'day'    => $oh->getDay(),
                    'label'  => $this->localizedDayOfWeek($oh->getDay()),
                    'ranges' => $oh->getRanges(),
                ];
            }

            return $data;
        }

        return parent::normalize($object, $format, $context);
    }

    /**
     * Returns the localized day of week.
     *
     * @param int $dayOfWeek
     *
     * @return string
     */
    protected function localizedDayOfWeek(int $dayOfWeek): string
    {
        if (class_exists('\IntlDateFormatter')) {
            $date = new DateTime('2017-01-01'); // Starts sunday
            $date->modify('+' . $dayOfWeek . ' days');

            $formatter = IntlDateFormatter::create(
                $this->localeProvider->getCurrentLocale(),
                IntlDateFormatter::NONE,
                IntlDateFormatter::NONE,
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
}
