<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer;

use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

use function array_replace;

/**
 * Class BOMNormalizer
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BOMNormalizer extends ResourceNormalizer
{
    public function __construct(
        private readonly SubjectHelperInterface $subjectHelper
    ) {

    }

    /**
     * @inheritDoc
     *
     * @param BillOfMaterialsInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup('Summary', $context)) {
            $components = [];
            foreach ($object->getComponents() as $component) {
                $subject = $this->subjectHelper->resolve($component);
                $components[] = [
                    'reference'   => $subject->getReference(),
                    'designation' => (string)$subject,
                    'quantity' =>  $component->getQuantity(),
                ];
            }

            $subject = $this->subjectHelper->resolve($object);

            $data = array_replace($data, [
                'subject'    => [
                    'reference'   => $subject->getReference(),
                    'designation' => (string)$subject,
                ],
                'components' => $components,
            ]);
        }

        return $data;
    }
}
