<?php

use SpipRemix\Component\Orm\SqlQueryBuilder;

/**
 * Undocumented function.
 * 
 * @example https://github.com/spip-remix/database/blob/0.1/docs/select.md description
 *
 * @param string|array $from la table
 * @param array $select les colonnes de la table
 * @param string $where les conditions
 * @param array $groupby
 * @param string $orderBy
 * @param array $having
 * @param int $limit offset et position
 * @param string $connecteur le connecteur SQL
 * @param string $prefix le prefix des tables du site
 *
 * @return string la requête SQL
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
