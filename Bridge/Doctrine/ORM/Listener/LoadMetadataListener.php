<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing as Pricing;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
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
    private $paymentTermSubjectClassCache = [];

    /**
     * @var array
     */
    private $identityClassCache = [];

    /**
     * @var array
     */
    private $vatNumberSubjectClassCache = [];

    /**
     * @var array
     */
    private $taxableClassCache = [];

    /**
     * @var array
     */
    private $subjectRelativeClassCache = [];

    /**
     * @var array
     */
    private $subjectClassCache = [];

    /**
     * @var array
     */
    private $stockSubjectClassCache = [];


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
        // Skip mapped super classes
        if ($metadata->isMappedSuperclass) {
            return;
        }

        $class = $metadata->getName();
        // Skip abstract classes.
        if ((new \ReflectionClass($class))->isAbstract()) {
            return;
        }

        if (is_subclass_of($class, IdentityInterface::class)) {
            $this->configureIdentityMapping($eventArgs);
        }

        if (is_subclass_of($class, Pricing\Model\TaxableInterface::class)) {
            $this->configureTaxableMapping($eventArgs);
        }

        if (is_subclass_of($class, Pricing\Model\VatNumberSubjectInterface::class)) {
            $this->configureVatNumberSubjectMapping($eventArgs);
        }

        if (is_subclass_of($class, Payment\PaymentTermSubjectInterface::class)) {
            $this->configurePaymentTermSubjectMapping($eventArgs);
        }

        if (is_subclass_of($class, SubjectInterface::class)) {
            $this->configureSubjectMapping($eventArgs);
        } elseif (is_subclass_of($class, SubjectRelativeInterface::class)) {
            $this->configureSubjectRelativeMapping($eventArgs);
        }

        if (is_subclass_of($class, Stock\StockSubjectInterface::class)) {
            $this->configureStockSubjectMapping($eventArgs);
        }

        if (is_subclass_of($class, Stock\StockUnitInterface::class)) {
            $this->configureStockUnitDiscriminatorMap($eventArgs);
        }
    }

    /**
     * Configures the taxable mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureTaxableMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Check class
        if (!is_subclass_of($class, Pricing\Model\TaxableInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->taxableClassCache)) {
            return;
        }

        if (!$metadata->hasAssociation('taxGroup')) {
            $metadata->mapManyToOne([
                'fieldName'    => 'taxGroup',
                'targetEntity' => Pricing\Entity\TaxGroup::class,
                'joinColumns'  => [
                    [
                        'name'                 => 'tax_group_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'RESTRICT',
                        'nullable'             => true,
                    ],
                ],
            ]);
        }

        // Cache class
        $this->taxableClassCache[] = $class;
    }

    /**
     * Configures the identity mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureIdentityMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Check class
        if (!is_subclass_of($class, IdentityInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->identityClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getIdentityMappings());

        // Cache class
        $this->identityClassCache[] = $class;
    }

    /**
     * Configures the vat number subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureVatNumberSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Check class
        if (!is_subclass_of($class, Pricing\Model\VatNumberSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->vatNumberSubjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getVatNumberSubjectMappings());

        // Cache class
        $this->vatNumberSubjectClassCache[] = $class;
    }

    /**
     * Configures the payment term subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configurePaymentTermSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Check class
        if (!is_subclass_of($class, Payment\PaymentTermSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->paymentTermSubjectClassCache)) {
            return;
        }

        if (!$metadata->hasAssociation('paymentTerm')) {
            $metadata->mapManyToOne([
                'fieldName'    => 'paymentTerm',
                'targetEntity' => Payment\PaymentTermInterface::class,
                'joinColumns'  => [
                    [
                        'name'                 => 'payment_term_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'RESTRICT',
                        'nullable'             => true,
                    ],
                ],
            ]);
        }

        // Cache class
        $this->paymentTermSubjectClassCache[] = $class;
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

        // Check class
        if (!is_subclass_of($class, SubjectRelativeInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->subjectRelativeClassCache)) {
            return;
        }

        // Map embedded
        $this
            ->getSubjectIdentityMapper($eventArgs->getEntityManager())
            ->processClassMetadata($metadata, 'subjectIdentity', 'subject_');

        // Cache class
        $this->subjectRelativeClassCache[] = $class;
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

        // Check class
        if (!is_subclass_of($class, Stock\StockSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->stockSubjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getStockSubjectMappings());

        // Cache class
        $this->stockSubjectClassCache[] = $class;
    }

    /**
     * Configures the subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $class = $metadata->getName();

        // Check class
        if (!is_subclass_of($class, SubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($class, $this->subjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getSubjectMappings());

        // Cache class
        $this->subjectClassCache[] = $class;
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

        if (!is_subclass_of($metadata->name, Stock\StockUnitInterface::class)) {
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
     * Returns the vat number subject mappings.
     *
     * @return array
     */
    private function getIdentityMappings()
    {
        return [
            [
                'fieldName'  => 'gender',
                'columnName' => 'gender',
                'type'       => 'string',
                'length'     => 8,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'firstName',
                'columnName' => 'first_name',
                'type'       => 'string',
                'length'     => 64, // TODO length 32
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'lastName',
                'columnName' => 'last_name',
                'type'       => 'string',
                'length'     => 64, // TODO length 32
                'nullable'   => true,
            ],
        ];
    }

    /**
     * Returns the vat number subject mappings.
     *
     * @return array
     */
    private function getVatNumberSubjectMappings()
    {
        return [
            [
                'fieldName'  => 'vatNumber',
                'columnName' => 'vat_number',
                'type'       => 'string',
                'length'     => 32,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'vatDetails',
                'columnName' => 'vat_details',
                'type'       => 'json_array',
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'vatValid',
                'columnName' => 'vat_valid',
                'type'       => 'boolean',
            ],
        ];
    }

    /**
     * Returns the stock subject mappings.
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
                'nullable'   => false,
                'default'    => Stock\StockSubjectModes::MODE_AUTO,
            ],
            [
                'fieldName'  => 'stockState',
                'columnName' => 'stock_state',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => false,
                'default'    => Stock\StockSubjectStates::STATE_OUT_OF_STOCK,
            ],
            [
                'fieldName'  => 'stockFloor',
                'columnName' => 'stock_floor',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => true,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'inStock',
                'columnName' => 'in_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'availableStock',
                'columnName' => 'available_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'virtualStock',
                'columnName' => 'virtual_stock',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'replenishmentTime',
                'columnName' => 'replenishment_time',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 7,
            ],
            [
                'fieldName'  => 'estimatedDateOfArrival',
                'columnName' => 'estimated_date_of_arrival',
                'type'       => 'datetime',
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'geocode',
                'columnName' => 'geocode',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'minimumOrderQuantity',
                'columnName' => 'minimum_order_quantity',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 1,
            ],
            [
                'fieldName'  => 'quoteOnly',
                'columnName' => 'quote_only',
                'type'       => 'boolean',
                'nullable'   => false,
                'default'    => false,
            ],
            [
                'fieldName'  => 'endOfLife',
                'columnName' => 'end_of_life',
                'type'       => 'boolean',
                'nullable'   => false,
                'default'    => false,
            ],
            [
                'fieldName'  => 'unit',
                'columnName' => 'unit',
                'type'       => 'string',
                'length'     => 16,
                'nullable'   => true,
                'default'    => Units::PIECE,
            ],
            [
                'fieldName'  => 'weight',
                'columnName' => 'weight',
                'type'       => 'decimal',
                'precision'  => 7,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'width',
                'columnName' => 'width',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'height',
                'columnName' => 'height',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'depth',
                'columnName' => 'depth',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageWeight',
                'columnName' => 'package_weight',
                'type'       => 'decimal',
                'precision'  => 7,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageWidth',
                'columnName' => 'package_width',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageHeight',
                'columnName' => 'package_height',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageDepth',
                'columnName' => 'package_depth',
                'type'       => 'smallint',
                'nullable'   => false,
                'default'    => 0,
            ],
        ];
    }

    /**
     * Returns the stock subject mappings.
     *
     * @return array
     */
    private function getSubjectMappings()
    {
        return [
            [
                'fieldName'  => 'designation',
                'columnName' => 'designation',
                'type'       => 'string',
                'length'     => 128,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'reference',
                'columnName' => 'reference',
                'type'       => 'string',
                'length'     => 32,
                'unique'     => 'true',
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'netPrice',
                'columnName' => 'net_price',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 5,
                'nullable'   => true,
                'default'    => 0,
            ],
        ];
    }
}
