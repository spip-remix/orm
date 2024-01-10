<?php

/***************************************************************************\
 *  SPIP, Système de publication pour l'internet                           *
 *                                                                         *
 *  Copyright © avec tendresse depuis 2001                                 *
 *  Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.     *
\***************************************************************************/

/**
 * Gestion d'affichage de la page de destruction des tables de SPIP
 *
 * @package SPIP\Core\Base
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

/**
 * Destruction des tables SQL de SPIP
 *
 * La liste des tables à supprimer est à poster sur le nom (tableau) `delete`
 *
 * @pipeline_appel delete_tables
 * @param string $titre Inutilisé
 **/
function base_delete_all_dist($titre) {
	$delete = _request('delete');
	$res = [];
	if (is_array($delete)) {
		foreach ($delete as $table) {
			if (sql_drop_table($table)) {
				$res[] = $table;
			} else {
				spip_logger()->error("SPIP n'a pas pu detruire $table.");
			}
		}

		// un pipeline pour detruire les tables installees par les plugins
		pipeline('delete_tables', '');

		spip_unlink(_FILE_CONNECT);
		spip_unlink(_FILE_CHMOD);
		spip_unlink(_FILE_META);
		spip_unlink(_ACCESS_FILE_NAME);
		spip_unlink(_CACHE_RUBRIQUES);
	}
	$d = is_countable($delete) ? count($delete) : 0;
	$r = count($res);
	spip_logger()->notice("Tables detruites: $r sur $d: " . implode(', ', $res));
}
