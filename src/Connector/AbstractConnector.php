<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

use SpipRemix\Component\Orm\ConnectorInterface;
use SpipRemix\Component\Orm\NetworkInterface;

abstract class AbstractConnector implements ConnectorInterface
{
    protected mixed $handle;

    public function __construct(
        protected string $name,
        protected string $driver,
        private NetworkInterface $network,
        protected string $base = '',
        protected string $table_prefix = '',
        ?string $filename = null,
        ?string $socket = null,
        ?string $hostname = null,
        ?int $port = null,
        protected ?string $username = null,
        #[\SensitiveParameter]
        protected?string $password = null,
        protected array $options = [],
    ) {
    }

    abstract public static function getServer(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function geDriver(): string
    {
        return $this->driver;
    }

    public function getTablePrefix(): string
    {
        return $this->table_prefix;
    }

    public function connect(): mixed
    {
        if (\is_null($this->handle)) {
            $this->handle = new \PDO(
                $this->network->getPdoString(),
                $this->username,
                $this->password,
                $this->options
            );
        }

        return $this->handle;
    }

    public function query(string $query): mixed
    {
        return \null;
    }
}
