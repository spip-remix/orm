<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

final class MySqlConnector extends AbstractConnector
{
    public static function getDsnPrefix(): string
    {
        return 'mysql';
    }

    public static function getServer(): string
    {
        return '';
        // return $this->server;
    }
}
