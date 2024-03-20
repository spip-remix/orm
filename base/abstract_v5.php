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

use SpipRemix\Component\Orm\ConnectorInterface;
use SpipRemix\Component\Orm\Factory;
use SpipRemix\Component\Orm\SqlQueryBuilder;

function _service_orm(?string $connecteur = null): ConnectorInterface
{
    $connecteur = (is_null($connecteur) || $connecteur == '') ? 'spip' : $connecteur;

    static $connecteurs = [];
    static $dbalFactory = null;

    $GLOBALS['config'] = require_once __DIR__ . '/../bin/config/orm.php'; // []

    if (is_null($dbalFatrory)) {
        $dbalFactory = new Factory($GLOBALS['config']);
    }

    if (!isset($connecteurs[$connecteur])) {
        $connecteurs[$connecteur] = $dbalFactory->createConnector($connecteur)->connect();
        $connecteurs[$connecteur]->setQueryBuilder(SqlQueryBuilder::class);
    }

    return $connecteurs[$connecteur];
}

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
    ?string $prefix = null, // historique: 'spip', par défaut
): mixed {
    $connect = _service_orm($connecteur);
    return $connect->select(
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
    ?string $prefix = null, // historique: 'spip', par défaut
): mixed {
    $connect = _service_orm($connecteur);
    return $connect->insert(
        $table,
        $noms,
        $valeurs,
    );
}

function sql_update(
    string $table,
    string|array $valeurs = [],
    string|array $where = [],
    string $connecteur = 'spip',
    ?string $prefix = null, // historique: 'spip', par défaut
): mixed {
    $connect = _service_orm($connecteur);
    return $connect->update(
        $table,
        $valeurs,
        $where,
    );
}

function sql_delete(
    string $table,
    string|array $where = [],
    string $connecteur = 'spip',
    ?string $prefix = null, // historique: 'spip', par défaut
): mixed {
    $connect = _service_orm($connecteur);
    return $connect->delete(
        $table,
        $where,
    );
}
