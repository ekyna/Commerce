<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model\MentionInterface;
use Ekyna\Component\Commerce\Common\Model\MentionTranslationInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Resource\Model\AbstractTranslatable;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AbstractMention
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method MentionTranslationInterface translate($locale = null, $create = false)
 */
abstract class AbstractMention extends AbstractTranslatable implements MentionInterface
{
    use SortableTrait;

    protected ?int $id = null;
    /** @var string[] */
    protected array $documentTypes = [];


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentTypes(): array
    {
        return $this->documentTypes;
    }

    public function addDocumentType(string $type): MentionInterface
    {
        DocumentTypes::isValid($type);

        if (in_array($type, $this->documentTypes, true)) {
            return $this;
        }

        $this->documentTypes[] = $type;

        return $this;
    }

    public function setDocumentTypes(array $types): MentionInterface
    {
        $this->documentTypes = [];

        foreach ($types as $type) {
            $this->addDocumentType($type);
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->translate()->getContent();
    }
}
