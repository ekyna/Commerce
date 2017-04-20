<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Factory;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\TranslatableFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class AudienceFactory
 * @package Ekyna\Component\Commerce\Newsletter\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceFactory extends TranslatableFactory implements AudienceFactoryInterface
{
    private GeneratorInterface $keyGenerator;

    public function __construct(GeneratorInterface $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    public function create(): ResourceInterface
    {
        $audience =  parent::create();

        if (!$audience instanceof AudienceInterface) {
            throw new UnexpectedValueException($audience, AudienceInterface::class);
        }

        $this->generateKey($audience);

        return $audience;
    }

    public function generateKey(AudienceInterface $audience): void
    {
        if (!empty($audience->getKey())) {
            return;
        }

        $audience->setKey($this->keyGenerator->generate($audience));
    }
}
