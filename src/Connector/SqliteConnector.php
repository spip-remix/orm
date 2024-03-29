<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Connector;

final class SqliteConnector extends AbstractConnector
{
    public static function getDsnPrefix(): string
    {
        return self::getServer();
    }

    public static function getServer(): string
    {
        return 'sqlite';
    }

    protected function setDsn(): void
    {
    //     if (!($this->memory || $this->temporary) && \is_null($this->filename)) {
    //         throw new \Exception('faut un fichier !');
    //     }

    //     $this->dsn = 'sqlite:'.($this->temporary ?
    //         '' :
    //         ($this->memory ? ':memory:' : $this->filename));
    }
}
