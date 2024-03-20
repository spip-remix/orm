<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connection;

use SpipRemix\Component\Orm\ConnectionInterface;

/**
 * Undocumented interface.
 *
 * @author JamesRezo <james@rezo.net>
 */
final class Tcp implements ConnectionInterface
{
    /** @var non-empty-string */
    private string $uri;

    /** @var non-empty-string */
    private string $pdoString;

    /**
     * @param non-empty-string $driver
     * @param non-empty-string $hostname
     * @param non-empty-string $base
     * @param positive-int|null $port
     * @param string $username
     * @param string $password
     * @param ?string $alter_username
     * @param ?string $alter_password
     */
    public function __construct(
        string $driver,
        string $hostname,
        string $base,
        ?int $port = null,
        string $username = '',
        #[\SensitiveParameter]
        string $password = '',
        ?string $alter_username = null,
        #[\SensitiveParameter]
        ?string $alter_password = null,
    ) {
        $port = \is_null($port) ? '' : 'port=' . \strval($port) . ';';
        $this->pdoString = $driver . ':host=' . $hostname . ';' . $port . 'dbname=' . $base . ';';
    }

    public function getUri(): string
    {
        return $this->getPdoString();
    }

    public function getPdoString(): string
    {
        return $this->pdoString;
    }
}
