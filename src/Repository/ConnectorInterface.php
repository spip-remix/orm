<?php

namespace SpipRemix\Component\Orm\Repository;

interface ConnectorInterface
{
    public function getServer(): string;

    public function getName(): string;

    public function getPrefix(): string;

    public function connect(): mixed;

    public function query(): mixed;
}
