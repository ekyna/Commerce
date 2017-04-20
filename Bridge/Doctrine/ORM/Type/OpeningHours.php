<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Ekyna\Component\Commerce\Shipment\Model\OpeningHour;

use function is_array;

/**
 * Class OpeningHours
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OpeningHours extends JsonType
{
    public const NAME = 'opening_hours';

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $data = [];

        if (is_array($value)) {
            /** @var OpeningHour $oh */
            foreach ($value as $oh) {
                if (!$oh instanceof OpeningHour) {
                    throw ConversionException::conversionFailedSerialization(
                        $value, 'json', 'Expected instance of ' . OpeningHour::class
                    );
                }

                $datum = [
                    'd' => $oh->getDay(),
                    'r' => [],
                ];
                foreach ($oh->getRanges() as $r) {
                    $datum['r'][] = [
                        'f' => $r['from'],
                        't' => $r['to'],
                    ];
                }

                $data[] = $datum;
            }
        }

        return parent::convertToDatabaseValue($data, $platform);
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $val = parent::convertToPHPValue($value, $platform);

        if (!is_array($val)) {
            return [];
        }

        $data = [];

        foreach ($val as $datum) {
            $oh = new OpeningHour();
            $oh->setDay($datum['d']);

            foreach ($datum['r'] as $r) {
                $oh->addRanges($r['f'], $r['t']);
            }

            $data[] = $oh;
        }

        return $data;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
