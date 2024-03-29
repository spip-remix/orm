<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connection;

use SpipRemix\Component\Orm\ConnectionInterface;

/**
 * Undocumented interface.
 *
 * @author JamesRezo <james@rezo.net>
 */
final class Socket extends File implements ConnectionInterface
{
    /**
     * @param non-empty-string $driver
     * @param non-empty-string $socket
     * @param non-empty-string $base
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $driver,
        string $socket,
        string $base,
        string $username = '',
        #[\SensitiveParameter]
        string $password = '',
        ?string $alter_username = null,
        #[\SensitiveParameter]
        ?string $alter_password = null,
    ) {
        if (\str_contains($driver, 'mysql')) {
            $socket = 'unix_socket=' . $socket;
        }
        if (\str_contains($driver, 'pgsql')) {
            $socket = 'host=/tmp;port=5432; (' . $socket . ')';
        }
        $base = 'dbname=' . $base;
        parent::__construct($driver, $socket . ';' . $base . ';');
    }
}
