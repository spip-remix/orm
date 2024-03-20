<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm;

use SpipRemix\Component\Orm\Connector\MySqlConnector;
use SpipRemix\Component\Orm\Connector\PgSqlConnector;
use SpipRemix\Component\Orm\Connector\SqliteConnector;
use SpipRemix\Component\Orm\Exception\ConfigException;
use SpipRemix\Component\Orm\Exception\DriverException;
use SpipRemix\Component\Orm\Network\File;
use SpipRemix\Component\Orm\Network\Socket;
use SpipRemix\Component\Orm\Network\Tcp;

/**
 * Undocumented class.
 *
 * @author JamesRezo <james@rezo.net>
 */
class Factory
{
    /**
     * @example https://github.com/spip-remix/database/blob/0.1/docs/Connecteurs.md#configuration Exemples de configuration
     *
     * @param array{name:non-empty-string,array{driver:non-empty-string,parameters:array<string,mixed}} $config
     */
    public function __construct(private array $config) {}

    public const KNOWN_DRIVERS = [
        'pdo_sqlite' => SqliteConnector::class,
        // 'sqlite3' => SqliteConnector::class,
        'mysqli' => MySqlConnector::class,
        'pdo_mysql' => MySqlConnector::class,
        'pgsql' => PgSqlConnector::class,
        'pdo_pgsql' => PgSqlConnector::class,
    ];

    /**
     * @todo SSL Connexion.
     *
     * @param [type] ...$parameters
     * @return NetworkInterface
     */
    public function createNetwork(string $driver, ...$parameters): NetworkInterface
    {
        if (\str_contains($driver, 'sqlite')) {
            /**
             * @todo accept a writeable filename to create or update in a writeable directory.
             * @todo accept ':memory:' or '' if sqlite
             */
            return new File(SqliteConnector::getDsnPrefix(), $parameters['filename']);
        }

        if (\str_contains($driver, 'pgsql')) {
            return isset($parameters['socket']) ?
                new Socket(PgSqlConnector::getDsnPrefix(), ...$parameters) :
                new Tcp(PgSqlConnector::getDsnPrefix(), ...$parameters);
        }

        if (\str_contains($driver, 'mysql')) {
            return isset($parameters['socket']) ?
                new Socket(MySqlConnector::getDsnPrefix(), ...$parameters) :
                new Tcp(MySqlConnector::getDsnPrefix(), ...$parameters);
        }

        DriverException::throw($driver);
    }

    public function createConnector(string $name): ConnectorInterface
    {
        if (!\array_key_exists($name, $this->config)) {
            ConfigException::throw($name);
        }

        $driver = \strtolower($this->config[$name]['driver']);
        if (!\in_array($driver, \array_keys(self::KNOWN_DRIVERS))) {
            DriverException::throw($driver);
        }

        $class = self::KNOWN_DRIVERS[$driver];
        $network = $this->createNetwork($driver, ...$this->config[$name]['parameters']);

        return new $class($name, $driver, $network, ...$this->config[$name]['parameters']);
    }
}
