<?php

namespace Ekyna\Component\Commerce\Customer\Export;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CustomerExporter
 * @package Ekyna\Component\Commerce\Customer\Export
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerExporter
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $orderClass;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     * @param string                 $orderClass
     */
    public function __construct(EntityManagerInterface $manager, string $orderClass)
    {
        $this->manager    = $manager;
        $this->orderClass = $orderClass;
    }

    /**
     * Exports the customers data.
     *
     * @param CustomerExport $config
     *
     * @return string The export file path
     */
    public function export(CustomerExport $config): string
    {
        $qb = $this->manager->createQueryBuilder();

        $headers = [
            'id',
            'email',
            'first name',
            'last name',
            'company',
            'group',
            'revenue',
            'shipping',
        ];

        $select = [
            'c.id as id',
            'c.email as email',
            'c.firstName as first_name',
            'c.lastName as last_name',
            'c.company as company',
            'g.name as group',
            'SUM(o.netTotal) as revenue',
            'SUM(o.shipmentAmount) as shipping',
        ];

        $qb
            ->from($this->orderClass, 'o')
            ->join('o.customer', 'c')
            ->join('c.customerGroup', 'g')
            ->addGroupBy('c.id');

        /* TODO if ($config->isWithAddresses()) {
            $qb
                ->leftJoin('c.addresses', 'i', Expr\Join::WITH, 'i.invoiceDefault = 1')
                ->leftJoin('c.addresses', 'd', Expr\Join::WITH, 'i.deliveryDefault = 1');

            $headers += [
                // ...
            ];

            $select += [
                'i.street',
                'i.postalCode',
                'i.city',
                'i.phone',
                'i.mobile',
            ];
        }*/

        if (null !== $from = $config->getFrom()) {
            $qb
                ->andWhere($qb->expr()->gte('o.acceptedAt', ':from'))
                ->setParameter('from', $from, Types::DATE_MUTABLE);
        }

        if (null !== $to = $config->getTo()) {
            $qb
                ->andWhere($qb->expr()->lte('o.acceptedAt', ':to'))
                ->setParameter('to', $to, Types::DATE_MUTABLE);
        }

        if (0 < $config->getGroups()->count()) {
            $qb
                ->andWhere($qb->expr()->in('c.customerGroup', ':groups'))
                ->setParameter('groups', $config->getGroups()->toArray());
        }

        $lines = $qb
            ->select($select)
            ->getQuery()
            ->getScalarResult();

        $path = tempnam(sys_get_temp_dir(), 'customer_export');

        $handle = fopen($path, 'w');

        fputcsv($handle, $headers, ';');

        foreach ($lines as $line) {
            fputcsv($handle, $line, ';');
        }

        fclose($handle);

        return $path;
    }
}
