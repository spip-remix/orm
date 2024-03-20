<?php

namespace SpipRemix\Component\Orm;

final class Repository implements RepositoryInterface
{
    public function __construct(
        public readonly string $name,
        private ConnectorInterface $connector,
        private SqlQueryBuilderInterface $builder,
    ) {}

    public function __toString(): string
    {
        return $this->name;
    }

    public function select($from, $select, $where, $groupby, $having, $orderBy, $limit): mixed
    {
        return $this->connector->query($this->builder->select(
            $from, $select, $where, $groupby, $having, $orderBy, $limit
        ));
    }

    public function fetch(): mixed { return \null; }
}
