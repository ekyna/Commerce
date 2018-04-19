<?php

namespace Ekyna\Component\Commerce\Shipment\Gateway\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class Label
 * @package Ekyna\Component\Commerce\Shipment\Gateway\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated
 * @TODO remove use AbstractShipmentLabel
 */
class Label
{
    const FORMAT_PNG  = 'image/png';
    const FORMAT_GIF  = 'image/gif';
    const FORMAT_JPEG = 'image/jpeg';

    const SIZE_A6 = 'a6';
    const SIZE_A5 = 'a5';

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $size;


    /**
     * Constructor.
     *
     * @param string $content
     * @param string $format
     * @param string $size
     */
    public function __construct($content, string $format, string $size)
    {
        $this->content = is_resource($content) ? stream_get_contents($content) : $content;
        $this->format = $format;
        $this->size = $size;
    }

    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the format.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns the size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns all the sizes.
     *
     * @return array
     */
    public static function getSizes()
    {
        return [
            static::SIZE_A6,
            static::SIZE_A5,
        ];
    }

    /**
     * Returns whether the given size is valid.
     *
     * @param string $size
     * @param bool   $throw
     *
     * @return bool
     */
    public static function isValidSize($size, $throw = false)
    {
        if (in_array($size, static::getSizes(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException("Unknown size '$size'.");
        }

        return false;
    }

    /**
     * Returns all the formats.
     *
     * @return array
     */
    public static function getFormats()
    {
        return [
            static::FORMAT_PNG,
            static::FORMAT_GIF,
            static::FORMAT_JPEG,
        ];
    }

    /**
     * Returns whether the given format is valid.
     *
     * @param string $format
     * @param bool   $throw
     *
     * @return bool
     */
    public static function isValidFormat($format, $throw = false)
    {
        if (in_array($format, static::getFormats(), true)) {
            return true;
        }

        if ($throw) {
            throw new InvalidArgumentException("Unknown format '$format'.");
        }

        return false;
    }
}
