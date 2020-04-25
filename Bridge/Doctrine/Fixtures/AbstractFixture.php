<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture as BaseFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractFixture
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\Fixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFixture extends BaseFixture implements OrderedFixtureInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $accessor;


    /**
     * Configures the fixtures loader.
     *
     * @return array
     */
    abstract protected function configure();

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        $config = $this->configure();

        if (!isset($config['filename'])) {
            throw new \Exception("Missing 'filename' key in fixtures configuration.");
        }
        if (!isset($config['class'])) {
            throw new \Exception("Missing 'class' key in fixtures configuration.");
        }

        $this->manager = $manager;
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $fixtures = Yaml::parse(file_get_contents(__DIR__ . '/data/' . $config['filename']));

        $metadata = $this->manager->getClassMetadata($config['class']);

        foreach ($fixtures as $reference => $data) {
            $entity = $this->buildEntity($metadata, $data);

            $manager->persist($entity);

            $this->addReference($reference, $entity);
        }

        $manager->flush();
    }

    /**
     * Builds the entity
     *
     * @param ClassMetadata $metadata
     * @param array         $data
     *
     * @return object
     * @throws \Exception
     */
    private function buildEntity(ClassMetadata $metadata, $data)
    {
        $class = $metadata->getName();
        $entity = new $class;

        foreach ($data as $propertyPath => $value) {
            // Field
            if ($metadata->hasField($propertyPath)) {
                $builtValue = $this->buildFieldValue($metadata, $propertyPath, $value);

            // Association
            } elseif ($metadata->hasAssociation($propertyPath)) {
                $builtValue = $this->buildAssociationValue($metadata, $propertyPath, $value);

            } else {
                throw new \Exception("Unexpected property path '$propertyPath' for class '$class'.");
            }

            $this->accessor->setValue($entity, $propertyPath, $builtValue);
        }

        return $entity;
    }

    /**
     * Builds the field value.
     *
     * @param ClassMetadata $metadata
     * @param string        $propertyPath
     * @param string        $value
     *
     * @return mixed
     * @throws \Exception
     */
    private function buildFieldValue(ClassMetadata $metadata, $propertyPath, $value)
    {
        $type = $metadata->getTypeOfField($propertyPath);

        switch ($type) {
            case 'smallint':
            case 'integer':
            case 'bigint':
                if (!is_int($value)) {
                    throw new \Exception('Expected integer.');
                }
                return intval($value);

            case 'boolean':
                if (!is_bool($value)) {
                    throw new \Exception('Expected boolean.');
                }
                return (bool)$value;

            case 'float':
            case 'double':
            case 'decimal':
                if (!is_numeric($value)) {
                    throw new \Exception('Expected float.');
                }
                return floatval($value);

            case 'datetime':
                return new \DateTime($value);

            case 'string':
                return (string)$value;
        }

        throw new \Exception("Unsupported field type '$type' for path '$propertyPath'.");
    }

    /**
     * Builds the association value.
     *
     * @param ClassMetadata $metadata
     * @param string        $propertyPath
     * @param string        $value
     *
     * @return array|object
     * @throws \Exception
     */
    private function buildAssociationValue(ClassMetadata $metadata, $propertyPath, $value)
    {
        $childMetadata = $this->manager->getClassMetadata(
            $metadata->getAssociationTargetClass($propertyPath)
        );

        // Single association
        if ($metadata->isSingleValuedAssociation($propertyPath)) {
            if (is_string($value) && '#' === substr($value, 0, 1)) {
                return $this->getReference(substr($value, 1));
            } elseif (is_array($value)) {
                return $this->buildEntity($childMetadata, $value);
            }
            throw new \Exception("Unexpected value for single association '$propertyPath'.");

        // Collection association
        } elseif ($metadata->isCollectionValuedAssociation($propertyPath)) {
            if (!is_array($value)) {
                throw new \Exception('Expected array.');
            }
            $builtValue = [];
            foreach ($value as $childData) {
                if (is_string($childData) && '#' === substr($childData, 0, 1)) {
                    array_push($builtValue, $this->getReference(substr($childData, 1)));
                } elseif (is_array($value)) {
                    array_push($builtValue, $this->buildEntity($childMetadata, $childData));
                } else {
                    throw new \Exception("Unexpected value for association '$propertyPath'.");
                }
            }
            return $builtValue;
        }
        throw new \Exception("Unexpected association path '$propertyPath'.");
    }
}
