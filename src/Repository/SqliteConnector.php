<?php

namespace SpipRemix\Component\Orm\Repository;

final class SqliteConnector implements ConnectorInterface
{
    public function __construct(
        private string $name = 'spip',
        private string $prefix = 'spip',
    ) {
    }

    public function getServer(): string
    {
        return 'sqlite';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function connect(): mixed { return \null; }

    public function query(): mixed { return \null; }
}
