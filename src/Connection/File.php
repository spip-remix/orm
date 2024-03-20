<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connection;

use SpipRemix\Component\Orm\ConnectionInterface;

/**
 * Undocumented class.
 *
 * @author JamesRezo <james@rezo.net>
 */
class File implements ConnectionInterface
{
    /** @var non-empty-string */
    protected string $pdoString;

    /**
     * @param non-empty-string $driver
     * @param string $filename
     */
    public function __construct(
        string $driver,
        string $filename,
    ) {
        $this->pdoString = $driver . ':' . $filename;
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
