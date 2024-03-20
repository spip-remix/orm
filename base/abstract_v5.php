<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

declare(strict_types=1);

use SpipRemix\Component\Orm\SqlQueryBuilder;

/**
 * Undocumented function.
 *
 * @example https://github.com/spip-remix/database/blob/0.1/docs/select.md description
 *
 * @see 
 */
function sql_select(
    string|array $from,
    string|array $select = ['*'],
    string|array $where = '',
	string|array $groupby = [],
    string|array $orderBy = '',
	string|array $having = [],
    int|array $limit = 0,
    string $connecteur = 'spip',
    string $prefix = 'spip',
): string {
    return (new SqlQueryBuilder($prefix))->select(
        $from,
        $select,
        $where,
        $groupby,
        $orderBy,
        $having,
        $limit,
    );
}

function sql_insert(
    string $table,
    string|array $noms,
    string|array $valeurs,
    string $connecteur = 'spip',
    string $prefix = 'spip',
): string {
    return (new SqlQueryBuilder($prefix))->insert(
        $table,
        $noms,
        $valeurs,
    );
}
