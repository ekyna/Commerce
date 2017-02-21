<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Mapping\DiscriminatorMapper;
use Ekyna\Component\Resource\Doctrine\ORM\Mapping\EmbeddableMapper;

/**
 * Class LoadMetadataListener
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LoadMetadataListener implements EventSubscriber
{
    /**
     * @var DiscriminatorMapper
     */
    private $stockUnitMapper;

    /**
     * @var EmbeddableMapper
     */
    private $subjectIdentityMapper;

    /**
     * @var array
     */
    private $relativeClassCache = [];

    /**
     * @var array
     */
    private $stockClassCache = [];


    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Skip abstract classes.
        if ((new \ReflectionClass($class))->isAbstract()) {
            return;
        }

        if (is_subclass_of($class, StockSubjectInterface::class)) {
            $this->configureStockSubjectMapping($eventArgs);
        }

        if (is_subclass_of($class, SubjectRelativeInterface::class)) {
            $this->configureSubjectRelativeMapping($eventArgs);
        }

        if (is_subclass_of($class, StockUnitInterface::class)) {
            $this->configureStockUnitDiscriminatorMap($eventArgs);
        }
    }

    /**
     * Configures the subject relative mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureSubjectRelativeMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Don't add twice
        if (in_array($class, $this->relativeClassCache)) {
            return;
        }

        // Check class
        if (!is_subclass_of($class, SubjectRelativeInterface::class)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getSubjectRelativeMappings());

        // Map embedded
        $this
            ->getSubjectIdentityMapper($eventArgs->getEntityManager())
            ->processClassMetadata($metadata, 'subjectIdentity', 'subject_');

        // Cache class
        $this->relativeClassCache[] = $class;
    }

    /**
     * Configures the stock subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureStockSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Don't add twice
        if (in_array($class, $this->stockClassCache)) {
            return;
        }

        // Check class
        if (!is_subclass_of($class, StockSubjectInterface::class)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getStockSubjectMappings());

        // Cache class
        $this->stockClassCache[] = $class;
    }

    /**
     * Configures the stock unit discriminator map.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureStockUnitDiscriminatorMap(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if (!is_subclass_of($metadata->name, StockUnitInterface::class)) {
            return;
        }

        $this
            ->getStockUnitMapper($eventArgs->getEntityManager())
            ->processClassMetadata($metadata);
    }

    /**
     * Returns the stock unit mapper.
     *
     * @param EntityManagerInterface $em
     *
     * @return DiscriminatorMapper
     */
    private function getStockUnitMapper(EntityManagerInterface $em)
    {
        if (null === $this->stockUnitMapper) {
            $this->stockUnitMapper = new DiscriminatorMapper($em, AbstractStockUnit::class);
        }

        return $this->stockUnitMapper;
    }

    /**
     * Returns the subjectIdentityMapper.
     *
     * @param EntityManagerInterface $em
     *
     * @return EmbeddableMapper
     */
    private function getSubjectIdentityMapper(EntityManagerInterface $em)
    {
        if (null === $this->subjectIdentityMapper) {
            $this->subjectIdentityMapper = new EmbeddableMapper($em, SubjectIdentity::class);
        }

        return $this->subjectIdentityMapper;
    }

    /**
     * Adds the mappings to the metadata.
     *
     * @param ClassMetadata $metadata
     * @param array         $mappings
     */
    private function addMappings(ClassMetadata $metadata, array $mappings)
    {
        foreach ($mappings as $mapping) {
            if (!$metadata->hasField($mapping['fieldName'])) {
                $metadata->mapField($mapping);
            }
        }
    }

    /**
     * Returns the subject relative mappings.
     *
     * @return array
     */
    private function getSubjectRelativeMappings()
    {
        return [
            /*[
                'fieldName'  => 'subjectProvider',
                'columnName' => 'subject_provider',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'subjectIdentifier',
                'columnName' => 'subject_identifier',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
            ],*/
            [
                'fieldName'  => 'subjectData',
                'columnName' => 'subject_data',
                'type'       => 'json_array',
                'nullable'   => true,
            ],
        ];
    }

    /**
     * Returns the stck subject mappings.
     *
     * @return array
     */
    private function getStockSubjectMappings()
    {
        return [
            [
                'fieldName'  => 'stockMode',
                'columnName' => 'stock_mode',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'stockState',
                'columnName' => 'stock_state',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'inStock',
                'columnName' => 'in_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => true,
                /*'options'    => [
                    'unsigned' => true,
                    'default'  => 0,
                ],*/
            ],
            [
                'fieldName'  => 'orderedStock',
                'columnName' => 'ordered_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => true,
                /*'options'    => [
                    'unsigned' => true,
                    'default'  => 0,
                ],*/
            ],
            [
                'fieldName'  => 'shippedStock',
                'columnName' => 'shipped_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => true,
                /*'options'    => [
                    'unsigned' => true,
                    'default'  => 0,
                ],*/
            ],
            [
                'fieldName'  => 'estimatedDateOfArrival',
                'columnName' => 'estimated_date_of_arrival',
                'type'       => 'datetime',
                'nullable'   => true,
            ],
        ];
    }

}
