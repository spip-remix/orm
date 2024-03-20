<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm;

/**
 * Undocumented interface.
 *
 * @author JamesRezo <james@rezo.net>
 */
interface NetworkInterface
{
    /**
     * Undocumented function.
     *
     * @return non-empty-string
     */
    public function getUri(): string;

    /**
     * Undocumented function.
     *
     * @return non-empty-string
     */
    public function getPdoString(): string;
}
