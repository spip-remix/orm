<?php

namespace SpipRemix\Component\Orm\Repository;

final class MySqlConnector implements ConnectorInterface
{
    private string $dsn;

    public function __construct(
        private string $name = 'spip',
        string $host = 'localhost',
        int $port = 3306,
        string $base = 'spip',
        private string $prefix = 'spip',
        private string $server = 'mariadb', // mysql
        private string $driver = 'mysqli', // PDO, mysqlnd, ...
        private ?string $user = 'root',
        private ?string $password = '',
        private ?string $socket = \null,
    ) {
        $this->dsn = 'mysql://'.$host.':'.\strval($port).'/'.$base;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function connect(): mixed
    {
        $this->dsn;$this->driver;$this->user;$this->password;$this->socket;

        return null;
    }

    public function query(): mixed
    {
        echo 'on passe par le connecteur mysql' . PHP_EOL;

        return null;
    }
}
