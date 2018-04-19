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
            throw ConversionException::conversionFailedSerialization($data, 'json', $this->getLastErrorMessage());
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

    /**
     * Get the latest json error message
     *
     * This method declaration has been extracted from symfony's php 5.5 polyfill
     *
     * @link https://github.com/symfony/polyfill-php55/blob/master/Php55.php
     * @link http://nl1.php.net/manual/en/function.json-last-error-msg.php
     *
     * @return string
     */
    private function getLastErrorMessage()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'No error';

            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';

            case JSON_ERROR_STATE_MISMATCH:
                return 'State mismatch (invalid or malformed JSON)';

            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error, possibly incorrectly encoded';

            case JSON_ERROR_SYNTAX:
                return 'Syntax error';

            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';

            default:
                return 'Unknown error';
        }
    }
}