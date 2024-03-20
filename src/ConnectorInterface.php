<?php

namespace SpipRemix\Component\Orm;

interface ConnectorInterface
{
    public static function getDsnPrefix(): string;

    public static function getServer(): string;

    public function getName(): string;

    public function geDriver(): string;

    public function getTablePrefix(): string;

    public function connect(): mixed;

    public function query(string $query): mixed;
}
