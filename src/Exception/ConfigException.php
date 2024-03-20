<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm\Exception;

use SpipRemix\Contracts\Exception\ExceptionInterface;

class ConfigException extends \UnexpectedValueException implements ExceptionInterface
{
    public static function throw(string ...$context): static
    {
        return new static(sprintf('Configuration "%s" inexistante.', ...$context));
    }
}
