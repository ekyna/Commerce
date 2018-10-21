<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Commerce\Shipment\Model\OpeningHour;

/**
 * Class OpeningHours
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OpeningHours extends Type
{
    const NAME = 'opening_hours';


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

        $encoded = json_encode($data);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailedSerialization($data, 'json', json_last_error_msg());
        }

        return $encoded;
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $val = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ConversionException::conversionFailed($value, $this->getName());
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

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
