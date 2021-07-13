<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

use EMS\CommonBundle\Common\Standard\Json;
use function EMS\CommonBundle\Common\Standard\Json;

/**
 * @implements \IteratorAggregate<JsonMenuNested>
 */
final class JsonMenuNested implements \IteratorAggregate
{
    private string $id;
    private string$type;
    private string $label;
    /** @var array<mixed> */
    private array $object;
    /** @var JsonMenuNested[] */
    private array $children = [];
    /** @var JsonMenuNested|null */
    private ?JsonMenuNested $parent = null;

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->label = $data['label'] ?? '';
        $this->object = $data['object'] ?? [];

        $children = $data['children'] ?? [];

        foreach ($children as $child) {
            $childItem = new JsonMenuNested($child);
            $childItem->setParent($this);

            $this->children[] = $childItem;
        }
    }

    public static function fromStructure(string $structure): JsonMenuNested
    {
        return new self([
           'id' => 'root',
           'type' => 'root',
           'label' => 'root',
           'children' => Json::decode($structure),
        ]);
    }

    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * Return a flat array.
     *
     * @return array<JsonMenuNested>
     */
    public function toArray(): array
    {
        $data = [$this];

        foreach ($this->children as $child) {
            $data = \array_merge($data, $child->toArray());
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayStructure(bool $includeRoot = false): array
    {
        $children = $this->children;
        $structureChildren = \array_map(fn (JsonMenuNested $c) => $c->toArrayStructure(true), $children);

        if (!$includeRoot) {
            return $structureChildren;
        }

        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'object' => $this->object,
            'children' => \array_map(fn (JsonMenuNested $c) => $c->toArrayStructure(true), $children),
        ];
    }

    /**
     * Loop through the children recursively.
     */
    public function getIterator()
    {
        foreach ($this->children as $child) {
            yield $child;

            if ($child->hasChildren()) {
                yield from $child;
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<mixed>
     */
    public function getObject(): array
    {
        return $this->object;
    }

    /**
     * @return JsonMenuNested[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return JsonMenuNested[]
     */
    public function getPath(): array
    {
        $path = [$this];

        if (null !== $this->parent && !$this->parent->isRoot()) {
            $path = \array_merge($this->parent->getPath(), $path);
        }

        return $path;
    }

    public function getParent(): ?JsonMenuNested
    {
        return $this->parent;
    }

    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param array<string, mixed> $object
     */
    public function setObject(array $object): void
    {
        $this->object = $object;
    }

    public function setParent(?JsonMenuNested $parent): void
    {
        $this->parent = $parent;
    }
}
