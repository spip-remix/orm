<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

use SpipRemix\Component\Orm\ConnectorInterface;

final class PgSqlConnector extends AbstractConnector implements ConnectorInterface
{
    public static function getDsnPrefix(): string
    {
        return self::getServer();
    }

    public static function getServer(): string
    {
        return 'pgsql';
    }
}
