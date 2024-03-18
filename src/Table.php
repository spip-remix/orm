<?php

namespace SpipRemix\Component\Orm;

final class Table
{
    /**
     * Undocumented function
     *
     * @param array<string,mixed> $fields
     * @param array<string,mixed> $keys
     */
    public function __construct(
        public readonly array $fields = [],
        public readonly array $keys = [],
    ) {}

    /**
     * Undocumented function
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'field' => $this->fields,
            'key' => $this->keys,
        ];
    }
}
