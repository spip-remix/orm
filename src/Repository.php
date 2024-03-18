<?php

namespace SpipRemix\Component\Orm;

use SpipRemix\Component\Orm\Repository\ConnectorInterface;

final class Repository implements RepositoryInterface
{
    public function __construct(
        public readonly string $name,
        private ConnectorInterface $connector,
    ) {}

    public function __toString(): string
    {
        return $this->name;
    }

    public function select(): mixed
    {
        return $this->connector->query();
    }

    public function fetch(): mixed { return \null; }
}
