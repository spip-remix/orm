<?php

declare(strict_types=1);

namespace SpipRemix\Component\Orm;

/**
 * Undocumented interface.
 *
 * @todo Gérer d'autres paramètres pour la chaîne de connexion (ex: charset)
 *
 * @author JamesRezo <james@rezo.net>
 */
interface ConnectionInterface
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
