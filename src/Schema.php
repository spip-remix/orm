<?php

namespace SpipRemix\Component\Orm;

/**
 * Undocumented class.
 *
 * @template TKey of string
 * @template TValue of Table
 * @implements \ArrayAccess<TKey, TValue>
 */
final class Schema implements \ArrayAccess
{
    /** @var array<string,Table> $tables */
    private array $tables = [];

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->tables);
    }

    /**
     * Undocumented function.
     *
     * @return Table|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->tables[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->tables[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->tables[$offset]);
    }

    /**
     * Undocumented function.
     *
     * @return array<string,array<string,mixed>>
     */
    public function all(): array
    {
        return array_map(fn (Table $table) => $table->toArray(), $this->tables);
    }
}
