<?php

namespace SpipRemix\Component\Orm;

interface ConnectorInterface extends SqlQueryBuilderInterface
{
    public function setQueryBuilder(string|SqlQueryBuilderInterface $queryBuilder): void;

    public static function getDsnPrefix(): string;

    public static function getServer(): string;

    public function getName(): string;

    public function getDriver(): string;

    public function getTablePrefix(): string;

    public function connect(): static;

    public function alter_connect(): static;

    public function query(string $query, ?string $class = null): mixed;

    // public function get(): mixed;

    // public function all(): mixed;
}
