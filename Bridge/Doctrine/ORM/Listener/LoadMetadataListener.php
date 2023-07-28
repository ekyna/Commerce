<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\MarginSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Pricing as Pricing;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectReferenceInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectRelativeInterface;
use Ekyna\Component\Resource\Doctrine\DBAL\Type\PhpDecimalType;
use Ekyna\Component\Resource\Doctrine\ORM\Mapping\DiscriminatorMapper;
use Ekyna\Component\Resource\Doctrine\ORM\Mapping\EmbeddableMapper;

/**
 * Class LoadMetadataListener
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LoadMetadataListener
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
     * @var EmbeddableMapper
     */
    private $marginIdentityMapper;

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
    private $subjectIdentityClassCache = [];

    /**
     * @var array
     */
    private $marginSubjectClassCache = [];

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
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        // Skip mapped super classes
        if ($metadata->isMappedSuperclass) {
            return;
        }

        $rc = $metadata->getReflectionClass();

        // Skip abstract classes.
        if ($rc->isAbstract()) {
            return;
        }

        if ($rc->implementsInterface(IdentityInterface::class)) {
            $this->configureIdentityMapping($eventArgs);
        }

        if ($rc->implementsInterface(Pricing\Model\TaxableInterface::class)) {
            $this->configureTaxableMapping($eventArgs);
        }

        if ($rc->implementsInterface(Pricing\Model\VatNumberSubjectInterface::class)) {
            $this->configureVatNumberSubjectMapping($eventArgs);
        }

        if ($rc->implementsInterface(Payment\PaymentTermSubjectInterface::class)) {
            $this->configurePaymentTermSubjectMapping($eventArgs);
        }

        if ($rc->implementsInterface(SubjectInterface::class)) {
            $this->configureSubjectMapping($eventArgs);
        } elseif ($rc->implementsInterface(SubjectReferenceInterface::class)) {
            $this->configureSubjectIdentityMapping($eventArgs);

            if ($rc->implementsInterface(SubjectRelativeInterface::class)) {
                $this->configureSubjectRelativeMapping($eventArgs);
            }
        }

        if ($rc->implementsInterface(MarginSubjectInterface::class)) {
            $this->configureMarginMapping($eventArgs);
        }

        if ($rc->implementsInterface(Stock\StockSubjectInterface::class)) {
            $this->configureStockSubjectMapping($eventArgs);
        }

        if ($rc->implementsInterface(Stock\StockUnitInterface::class)) {
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
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(Pricing\Model\TaxableInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->taxableClassCache)) {
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
        $this->taxableClassCache[] = $metadata->getName();
    }

    /**
     * Configures the identity mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureIdentityMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(IdentityInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->identityClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getIdentityMappings());

        // Cache class
        $this->identityClassCache[] = $metadata->getName();
    }

    /**
     * Configures the vat number subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureVatNumberSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(Pricing\Model\VatNumberSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->vatNumberSubjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getVatNumberSubjectMappings());

        // Cache class
        $this->vatNumberSubjectClassCache[] = $metadata->getName();
    }

    /**
     * Configures the payment term subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configurePaymentTermSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(Payment\PaymentTermSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->paymentTermSubjectClassCache)) {
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
        $this->paymentTermSubjectClassCache[] = $metadata->getName();
    }

    /**
     * Configures the subject identity mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureSubjectIdentityMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $rc = $metadata->getReflectionClass();

        // Check class
        if (!$rc->implementsInterface(SubjectReferenceInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->subjectIdentityClassCache)) {
            return;
        }

        // Map embedded
        $this
            ->getSubjectIdentityMapper($eventArgs->getEntityManager())
            ->processClassMetadata($metadata, 'subjectIdentity', 'subject_');

        // Cache class
        $this->subjectIdentityClassCache[] = $metadata->getName();
    }

    /**
     * Configures the margin mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureMarginMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $rc = $metadata->getReflectionClass();

        // Check class
        if (!$rc->implementsInterface(MarginSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->marginSubjectClassCache)) {
            return;
        }

        // Map embedded
        $this
            ->getMarginMapper($eventArgs->getEntityManager())
            ->processClassMetadata($metadata, 'margin'); // Embed without prefix

        // Cache class
        $this->marginSubjectClassCache[] = $metadata->getName();
    }

    /**
     * Configures the subject relative mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureSubjectRelativeMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(SubjectRelativeInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->subjectRelativeClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getSubjectRelativeMappings());

        // Cache class
        $this->subjectRelativeClassCache[] = $metadata->getName();
    }

    /**
     * Configures the stock subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureStockSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!$metadata->getReflectionClass()->implementsInterface(Stock\StockSubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->stockSubjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getStockSubjectMappings());

        // Cache class
        $this->stockSubjectClassCache[] = $metadata->getName();
    }

    /**
     * Configures the subject mapping.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureSubjectMapping(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        // Check class
        if (!is_subclass_of($metadata->getName(), SubjectInterface::class)) {
            return;
        }

        // Don't add twice
        if (in_array($metadata->getName(), $this->subjectClassCache)) {
            return;
        }

        // Add mappings
        $this->addMappings($metadata, $this->getSubjectMappings());

        // Cache class
        $this->subjectClassCache[] = $metadata->getName();
    }

    /**
     * Configures the stock unit discriminator map.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    private function configureStockUnitDiscriminatorMap(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if (!$metadata->getReflectionClass()->implementsInterface(Stock\StockUnitInterface::class)) {
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
            $driver = $em->getConfiguration()->getMetadataDriverImpl();
            $this->stockUnitMapper = new DiscriminatorMapper($driver, AbstractStockUnit::class);
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
     * Returns the subjectIdentityMapper.
     *
     * @param EntityManagerInterface $em
     *
     * @return EmbeddableMapper
     */
    private function getMarginMapper(EntityManagerInterface $em): EmbeddableMapper
    {
        if (null === $this->marginIdentityMapper) {
            $this->marginIdentityMapper = new EmbeddableMapper($em, Margin::class);
        }

        return $this->marginIdentityMapper;
    }

    /**
     * Adds the mappings to the metadata.
     *
     * @param ClassMetadata $metadata
     * @param array         $mappings
     */
    private function addMappings(ClassMetadata $metadata, array $mappings): void
    {
        foreach ($mappings as $mapping) {
            if (!$metadata->hasField($mapping['fieldName'])) {
                $metadata->mapField($mapping);
            }
        }
    }

    /**
     * Returns the vat number subject mappings.
     */
    private function getIdentityMappings(): array
    {
        return [
            [
                'fieldName'  => 'gender',
                'columnName' => 'gender',
                'type'       => Types::STRING,
                'length'     => 8,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'firstName',
                'columnName' => 'first_name',
                'type'       => Types::STRING,
                'length'     => 64, // TODO length 32
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'lastName',
                'columnName' => 'last_name',
                'type'       => Types::STRING,
                'length'     => 64, // TODO length 32
                'nullable'   => true,
            ],
        ];
    }

    /**
     * Returns the vat number subject mappings.
     */
    private function getVatNumberSubjectMappings(): array
    {
        return [
            [
                'fieldName'  => 'vatNumber',
                'columnName' => 'vat_number',
                'type'       => Types::STRING,
                'length'     => 32,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'vatDetails',
                'columnName' => 'vat_details',
                'type'       => Types::JSON,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'vatValid',
                'columnName' => 'vat_valid',
                'type'       => Types::BOOLEAN,
            ],
        ];
    }

    /**
     * Returns the subject relative mappings.
     */
    private function getSubjectRelativeMappings(): array
    {
        return [
            [
                'fieldName'  => 'designation',
                'columnName' => 'designation',
                'type'       => Types::STRING,
                'length'     => 255,
                'nullable'   => false,
            ],
            [
                'fieldName'  => 'reference',
                'columnName' => 'reference',
                'type'       => Types::STRING,
                'length'     => 32,
                'nullable'   => false,
            ],
            [
                'fieldName'  => 'netPrice',
                'columnName' => 'net_price',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 15,
                'scale'      => 5,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'weight',
                'columnName' => 'weight',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 7,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'physical',
                'columnName' => 'physical',
                'type'       => Types::BOOLEAN,
                'nullable'   => false,
                'default'    => true,
            ],
            [
                'fieldName'  => 'unit',
                'columnName' => 'unit',
                'type'       => Types::STRING,
                'length'     => 16,
                'nullable'   => true,
                'default'    => Units::PIECE,
            ],
        ];
    }

    /**
     * Returns the stock subject mappings.
     */
    private function getStockSubjectMappings(): array
    {
        return [
            [
                'fieldName'  => 'stockMode',
                'columnName' => 'stock_mode',
                'type'       => Types::STRING,
                'length'     => 16,
                'nullable'   => false,
                'default'    => Stock\StockSubjectModes::MODE_AUTO,
            ],
            [
                'fieldName'  => 'stockState',
                'columnName' => 'stock_state',
                'type'       => Types::STRING,
                'length'     => 16,
                'nullable'   => false,
                'default'    => Stock\StockSubjectStates::STATE_OUT_OF_STOCK,
            ],
            [
                'fieldName'  => 'stockFloor',
                'columnName' => 'stock_floor',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => true,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'inStock',
                'columnName' => 'in_stock',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'availableStock',
                'columnName' => 'available_stock',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'virtualStock',
                'columnName' => 'virtual_stock',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'replenishmentTime',
                'columnName' => 'replenishment_time',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 7,
            ],
            [
                'fieldName'  => 'estimatedDateOfArrival',
                'columnName' => 'estimated_date_of_arrival',
                'type'       => Types::DATETIME_MUTABLE,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'geocode',
                'columnName' => 'geocode',
                'type'       => Types::STRING,
                'length'     => 16,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'minimumOrderQuantity',
                'columnName' => 'minimum_order_quantity',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 1,
            ],
            [
                'fieldName'  => 'releasedAt',
                'columnName' => 'released_at',
                'type'       => Types::DATETIME_MUTABLE,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'quoteOnly',
                'columnName' => 'quote_only',
                'type'       => Types::BOOLEAN,
                'nullable'   => false,
                'default'    => false,
            ],
            [
                'fieldName'  => 'endOfLife',
                'columnName' => 'end_of_life',
                'type'       => Types::BOOLEAN,
                'nullable'   => false,
                'default'    => false,
            ],
            [
                'fieldName'  => 'physical',
                'columnName' => 'physical',
                'type'       => Types::BOOLEAN,
                'nullable'   => false,
                'default'    => true,
            ],
            [
                'fieldName'  => 'unit',
                'columnName' => 'unit',
                'type'       => Types::STRING,
                'length'     => 16,
                'nullable'   => true,
                'default'    => Units::PIECE,
            ],
            [
                'fieldName'  => 'weight',
                'columnName' => 'weight',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 7,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'width',
                'columnName' => 'width',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'height',
                'columnName' => 'height',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'depth',
                'columnName' => 'depth',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageWeight',
                'columnName' => 'package_weight',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 7,
                'scale'      => 3,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageWidth',
                'columnName' => 'package_width',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageHeight',
                'columnName' => 'package_height',
                'type'       => Types::SMALLINT,
                'nullable'   => false,
                'default'    => 0,
            ],
            [
                'fieldName'  => 'packageDepth',
                'columnName' => 'package_depth',
                'type'       => Types::SMALLINT,
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
    private function getSubjectMappings(): array
    {
        return [
            [
                'fieldName'  => 'designation',
                'columnName' => 'designation',
                'type'       => Types::STRING,
                'length'     => 128,
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'reference',
                'columnName' => 'reference',
                'type'       => Types::STRING,
                'length'     => 32,
                'unique'     => 'true',
                'nullable'   => true,
            ],
            [
                'fieldName'  => 'netPrice',
                'columnName' => 'net_price',
                'type'       => PhpDecimalType::NAME,
                'precision'  => 10,
                'scale'      => 5,
                'nullable'   => true,
                'default'    => 0,
            ],
        ];
    }
}
