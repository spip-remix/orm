<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

use SpipRemix\Component\Orm\ConnectorInterface;

final class MySqlConnector extends AbstractConnector implements ConnectorInterface
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
