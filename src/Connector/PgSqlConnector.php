<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

final class PgSqlConnector extends AbstractConnector
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
