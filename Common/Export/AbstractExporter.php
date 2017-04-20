<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Export;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class AbstractExporter
 * @package Ekyna\Component\Commerce\Common\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExporter
{
    protected PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Builds the CSV file.
     */
    protected function buildFile(array $objects, string $name, array $map): string
    {
        $rows = [];

        if (!empty($headers = $this->buildHeaders(array_keys($map)))) {
            $rows[] = $headers;
        }

        foreach ($objects as $object) {
            if (!empty($row = $this->buildRow($object, $map))) {
                $rows[] = $row;
            }
        }

        return $this->createFile($rows, $name);
    }

    /**
     * Creates the CSV file.
     */
    protected function createFile(array $rows, string $name): string
    {
        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        if (false === $handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Returns the headers.
     */
    protected function buildHeaders(array $names): array
    {
        $headers = [];

        foreach ($names as $name) {
            $headers[] = $this->buildHeader($name);
        }

        return $headers;
    }

    /**
     * Returns the header for the given name.
     */
    protected function buildHeader(string $name): string
    {
        return $name;
    }

    /**
     * Builds the row.
     *
     * @param mixed $object
     */
    protected function buildRow($object, array $map): array
    {
        $row = [];

        foreach ($map as $name => $value) {
            if (is_string($value)) {
                $value = $this->accessor->getValue($object, $value);
            } elseif (is_callable($value)) {
                $value = $value($object);
            } else {
                throw new UnexpectedValueException('Expected string or callable.');
            }

            $row[] = $this->transform($name, (string)$value);
        }

        return $row;
    }

    /**
     * Transforms the value.
     */
    protected function transform(string $name, string $value): ?string
    {
        return $value;
    }
}
