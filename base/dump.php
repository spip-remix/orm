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
 * Fonctions de base pour la sauvegarde
 *
 * Boîte à outil commune, sans préjuger de la méthode de sauvegarde
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

define('_VERSION_ARCHIVE', '1.3');

include_spip('base/serial');
include_spip('base/auxiliaires');
include_spip('public/interfaces'); // pour table_jointures

include_fichiers_fonctions();

/**
 * Retourne un nom de meta pour une rubrique et l'auteur connecté.
 *
 * Ce nom servira pour le stockage dans un fichier temporaire des informations
 * sérialisées sur le statut de l'export.
 *
 * @param int $rub
 * @return string
 **/
function base_dump_meta_name($rub) {
	return $meta = "status_dump_{$rub}_" . abs($GLOBALS['visiteur_session']['id_auteur']);
}

/**
 * Crée un répertoire recevant la sauvegarde de la base de données
 * et retourne son chemin.
 *
 * @note
 *   Utilisé uniquement dans l'ancienne sauvegarde XML (plugin dump_xml)
 *   À supprimer ?
 *
 * @param string $meta
 * @return string
 **/
function base_dump_dir($meta) {
	include_spip('inc/documents');
	// determine upload va aussi initialiser l'index "restreint"
	$maindir = determine_upload();
	if (!$GLOBALS['visiteur_session']['restreint']) {
		$maindir = _DIR_DUMP;
	}

	return sous_repertoire($maindir, $meta);
}

/**
 * Lister toutes les tables d'un serveur
 * en excluant eventuellement une liste fournie
 *
 * @param string $serveur
 * @param array $tables
 * @param array $exclude
 * @param bool $affiche_vrai_prefixe
 * @return array
 */
function base_lister_toutes_tables(
	$serveur = '',
	$tables = [],
	$exclude = [],
	$affiche_vrai_prefixe = false
) {
	spip_connect($serveur);
	$connexion = $GLOBALS['connexions'][$serveur ?: 0];
	$prefixe = $connexion['prefixe'];

	$p = '/^' . $prefixe . '/';
	$res = $tables;
	foreach (sql_alltable(null, $serveur) as $t) {
		if (preg_match($p, (string) $t)) {
			$t1 = preg_replace($p, 'spip', (string) $t);
			if (!in_array($t1, $tables) && !in_array($t1, $exclude)) {
				$res[] = ($affiche_vrai_prefixe ? $t : $t1);
			}
		}
	}
	sort($res);

	return $res;
}

/**
 * Retrouver le prefixe des tables
 *
 * @param string $serveur
 * @return string
 */
function base_prefixe_tables($serveur = '') {
	spip_connect($serveur);
	$connexion = $GLOBALS['connexions'][$serveur ?: 0];

	return $connexion['prefixe'];
}


/**
 * Fabrique la liste a cocher des tables a traiter (copie, delete, sauvegarde)
 *
 * @param string $name
 * @param array $tables
 * @param array $exclude
 * @param array|null $post
 * @param string $serveur
 * @return array
 */
function base_saisie_tables($name, $tables, $exclude = [], $post = null, $serveur = '') {
	include_spip('inc/filtres');
	$res = [];
	foreach ($tables as $k => $t) {
		// par defaut tout est coche sauf les tables dans $exclude
		$check = is_null($post) ? !in_array($t, $exclude) : in_array($t, $post);

		$res[$k] = "<input type='checkbox' value='$t' name='$name"
			. "[]' id='$name$k'"
			. ($check ? " checked='checked'" : '')
			. "/>\n"
			. "<label for='$name$k'>$t</label>"
			. ' ('
			. sinon(
				singulier_ou_pluriel(sql_countsel($t, '', '', '', $serveur), 'dump:une_donnee', 'dump:nb_donnees'),
				_T('dump:aucune_donnee')
			)
			. ')';
	}

	return $res;
}


/**
 * Lister les tables non exportables par defaut
 * (liste completable par le pipeline lister_tables_noexport
 *
 * @staticvar array $EXPORT_tables_noexport
 * @return array
 */
function lister_tables_noexport() {
	// par defaut tout est exporte sauf les tables ci-dessous
	static $EXPORT_tables_noexport = null;
	if (!is_null($EXPORT_tables_noexport)) {
		return $EXPORT_tables_noexport;
	}

	$EXPORT_tables_noexport = [
		'spip_caches', // plugin invalideur
		'spip_resultats', // resultats de recherche ... c'est un cache !
		'spip_test', // c'est un test !
		#'spip_referers',
		#'spip_referers_articles',
		#'spip_visites',
		#'spip_visites_articles',
		#'spip_versions',
		#'spip_versions_fragments'
	];

	$EXPORT_tables_noexport = pipeline('lister_tables_noexport', $EXPORT_tables_noexport);

	return $EXPORT_tables_noexport;
}

/**
 * Lister les tables non importables par defaut
 * (liste completable par le pipeline lister_tables_noimport
 *
 * @staticvar array $IMPORT_tables_noimport
 * @return array
 */
function lister_tables_noimport() {
	static $IMPORT_tables_noimport = null;
	if (!is_null($IMPORT_tables_noimport)) {
		return $IMPORT_tables_noimport;
	}

	$IMPORT_tables_noimport = [];
	// par defaut tout est importe sauf les tables ci-dessous
	// possibiliter de definir cela tables via la meta
	// compatibilite
	if (isset($GLOBALS['meta']['IMPORT_tables_noimport'])) {
		$IMPORT_tables_noimport = unserialize($GLOBALS['meta']['IMPORT_tables_noimport']);
		if (!is_array($IMPORT_tables_noimport)) {
			include_spip('inc/meta');
			effacer_meta('IMPORT_tables_noimport');
		}
	}
	$IMPORT_tables_noimport = pipeline('lister_tables_noimport', $IMPORT_tables_noimport);

	return $IMPORT_tables_noimport;
}


/**
 * Lister les tables a ne pas effacer
 * (liste completable par le pipeline lister_tables_noerase
 *
 * @staticvar array $IMPORT_tables_noerase
 * @return array
 */
function lister_tables_noerase() {
	static $IMPORT_tables_noerase = null;
	if (!is_null($IMPORT_tables_noerase)) {
		return $IMPORT_tables_noerase;
	}

	$IMPORT_tables_noerase = [
		'spip_meta',
		// par defaut on ne vide pas les stats, car elles ne figurent pas dans les dump
		// et le cas echeant, un bouton dans l'admin permet de les vider a la main...
		'spip_referers',
		'spip_referers_articles',
		'spip_visites',
		'spip_visites_articles'
	];
	$IMPORT_tables_noerase = pipeline('lister_tables_noerase', $IMPORT_tables_noerase);

	return $IMPORT_tables_noerase;
}


/**
 * construction de la liste des tables pour le dump :
 * toutes les tables principales
 * + toutes les tables auxiliaires hors relations
 * + les tables relations dont les deux tables liees sont dans la liste
 *
 * @param array $exclude_tables
 * @return array
 */
function base_liste_table_for_dump($exclude_tables = []) {
	$tables_for_dump = [];
	$tables_pointees = [];
	$tables = [];
	$tables_principales = $GLOBALS['tables_principales'];
	$tables_auxiliaires = $GLOBALS['tables_auxiliaires'];
	$tables_jointures = $GLOBALS['tables_jointures'];

	if (
		include_spip('base/objets')
		&& function_exists('lister_tables_objets_sql')
	) {
		$tables = lister_tables_objets_sql();
		foreach ($tables as $t => $infos) {
			if ($infos['principale'] && !isset($tables_principales[$t])) {
				$tables_principales[$t] = true;
			}
			if (!$infos['principale'] && !isset($tables_auxiliaires[$t])) {
				$tables_auxiliaires[$t] = true;
			}
			if (is_countable($infos['tables_jointures']) ? count($infos['tables_jointures']) : 0) {
				$tables_jointures[$t] = array_merge(
					$tables_jointures[$t] ?? [],
					$infos['tables_jointures']
				);
			}
		}
	}

	// on construit un index des tables de liens
	// pour les ajouter SI les deux tables qu'ils connectent sont sauvegardees
	$tables_for_link = [];
	foreach ($tables_jointures as $table => $liste_relations) {
		if (is_array($liste_relations)) {
			$nom = $table;
			if (!isset($tables_auxiliaires[$nom]) && !isset($tables_principales[$nom])) {
				$nom = "spip_$table";
			}
			if (isset($tables_auxiliaires[$nom]) || isset($tables_principales[$nom])) {
				foreach ($liste_relations as $link_table) {
					if (isset($tables_auxiliaires[$link_table])/*||isset($tables_principales[$link_table])*/) {
						$tables_for_link[$link_table][] = $nom;
					} else {
						if (isset($tables_auxiliaires["spip_$link_table"])/*||isset($tables_principales["spip_$link_table"])*/) {
							$tables_for_link["spip_$link_table"][] = $nom;
						}
					}
				}
			}
		}
	}

	$liste_tables = [...array_keys($tables_principales), ...array_keys($tables_auxiliaires), ...array_keys($tables)];
	foreach ($liste_tables as $table) {
		//		$name = preg_replace("{^spip_}","",$table);
		if (
			!isset($tables_pointees[$table])
			&& !in_array($table, $exclude_tables)
			&& !isset($tables_for_link[$table])
		) {
			$tables_for_dump[] = $table;
			$tables_pointees[$table] = 1;
		}
	}
	foreach ($tables_for_link as $link_table => $liste) {
		$connecte = true;
		foreach ($liste as $connect_table) {
			if (!in_array($connect_table, $tables_for_dump)) {
				$connecte = false;
			}
		}
		if ($connecte) {
			# on ajoute les liaisons en premier
			# si une restauration est interrompue,
			# cela se verra mieux si il manque des objets
			# que des liens
		array_unshift($tables_for_dump, $link_table);
		}
	}

	return [$tables_for_dump, $tables_for_link];
}

/**
 * Vider les tables de la base de destination
 * pour la copie dans une base
 *
 * peut etre utilise pour l'import depuis xml,
 * ou la copie de base a base (mysql<->sqlite par exemple)
 *
 * @param array $tables
 * @param array $exclure_tables
 * @param string $serveur
 */
function base_vider_tables_destination_copie($tables, $exclure_tables = [], $serveur = '') {
	$trouver_table = charger_fonction('trouver_table', 'base');

	spip_logger('base')->notice(
		'Vider ' . count($tables) . " tables sur serveur '$serveur' : " . implode(', ', $tables),
	);
	foreach ($tables as $table) {
		// sur le serveur principal, il ne faut pas supprimer l'auteur loge !
		if (!in_array($table, $exclure_tables) && ($table != 'spip_auteurs' || $serveur != '')) {
			// regarder si il y a au moins un champ impt='non'
			$desc = $trouver_table($table, $serveur);
			if (isset($desc['field']['impt'])) {
				sql_delete($table, "impt='oui'", $serveur);
			} elseif ($desc) {
				sql_delete($table, '', $serveur);
			}
		}
	}

	// sur le serveur principal, il ne faut pas supprimer l'auteur loge !
	// Bidouille pour garder l'acces admin actuel pendant toute la restauration
	if (
		$serveur == ''
		&& in_array('spip_auteurs', $tables)
		&& !in_array('spip_auteurs', $exclure_tables)
	) {
		base_conserver_copieur(true, $serveur);
		sql_delete('spip_auteurs', 'id_auteur>0', $serveur);
	}
}

/**
 * Conserver le copieur si besoin
 *
 * @param bool $move
 * @param string $serveur
 * @return void
 */
function base_conserver_copieur($move = true, $serveur = '') {
	// s'asurer qu'on a pas deja fait la manip !
	if ($GLOBALS['visiteur_session']['id_auteur'] > 0 && sql_countsel('spip_auteurs', 'id_auteur>0')) {
		spip_logger('dump')->notice(
			'Conserver copieur dans id_auteur=' . $GLOBALS['visiteur_session']['id_auteur'] . " pour le serveur '$serveur'",
		);
		sql_delete('spip_auteurs', 'id_auteur<0', $serveur);
		if ($move) {
			sql_updateq(
				'spip_auteurs',
				['id_auteur' => -$GLOBALS['visiteur_session']['id_auteur']],
				'id_auteur=' . (int) $GLOBALS['visiteur_session']['id_auteur'],
				[],
				$serveur
			);
		} else {
			$row = sql_fetsel(
				'*',
				'spip_auteurs',
				'id_auteur=' . $GLOBALS['visiteur_session']['id_auteur'],
				'',
				'',
				'',
				'',
				$serveur
			);
			$row['id_auteur'] = -$GLOBALS['visiteur_session']['id_auteur'];
			sql_insertq('spip_auteurs', $row, [], $serveur);
		}
	}
}

/**
 * Effacement de la bidouille ci-dessus
 * Toutefois si la table des auteurs ne contient plus qu'elle
 * c'est que la copie etait incomplete et on restaure le compte
 * pour garder la connection au site
 *
 * (mais il doit pas etre bien beau
 * et ca ne marche que si l'id_auteur est sur moins de 3 chiffres)
 *
 * @param string $serveur
 */
function base_detruire_copieur_si_besoin($serveur = '') {
	// rien a faire si ce n'est pas le serveur principal !
	if ($serveur == '') {
		if (sql_countsel('spip_auteurs', 'id_auteur>0')) {
			spip_logger('dump')->notice("Detruire copieur id_auteur<0 pour le serveur '$serveur'");
			sql_delete('spip_auteurs', 'id_auteur<0', $serveur);
		} else {
			spip_logger('dump')->notice(
				"Restaurer copieur id_auteur<0 pour le serveur '$serveur' (aucun autre auteur en base)",
			);
			sql_update('spip_auteurs', ['id_auteur' => '-id_auteur'], 'id_auteur<0');
		}
	} else {
		spip_logger('dump')->notice("Pas de destruction copieur sur serveur '$serveur'");
	}
}

/**
 * Preparer la table dans la base de destination :
 * la droper si elle existe (sauf si auteurs ou meta sur le serveur principal)
 * la creer si necessaire, ou ajouter simplement les champs manquants
 *
 * @param string $table
 * @param array $desc
 * @param string $serveur_dest
 * @param bool $init
 * @return array
 */
function base_preparer_table_dest($table, $desc, $serveur_dest, $init = false) {
	$upgrade = false;
	// si la table existe et qu'on est a l'init, la dropper
	if (($desc_dest = sql_showtable($table, true, $serveur_dest)) && $init) {
		if ($serveur_dest == '' && in_array($table, ['spip_meta', 'spip_auteurs'])) {
			// ne pas dropper auteurs et meta sur le serveur principal
			// faire un simple upgrade a la place
			// pour ajouter les champs manquants
			$upgrade = true;
			// coherence avec le drop sur les autres tables
			base_vider_tables_destination_copie([$table], [], $serveur_dest);
			if ($table == 'spip_meta') {
				// virer les version base qui vont venir avec l'import
				sql_delete($table, "nom like '%_base_version'", $serveur_dest);
				// hum casse la base si pas version_installee a l'import ...
				sql_delete($table, "nom='version_installee'", $serveur_dest);
			}
		} else {
			sql_drop_table($table, '', $serveur_dest);
			spip_logger('dump')->notice("drop table '$table' sur serveur '$serveur_dest'");
		}
		$desc_dest = false;
	}
	// si la table n'existe pas dans la destination, la creer a l'identique !
	if (!$desc_dest) {
		spip_logger('dump')->notice("creation '$table' sur serveur '$serveur_dest'");
		include_spip('base/create');
		creer_ou_upgrader_table($table, $desc, 'auto', $upgrade, $serveur_dest);
		$desc_dest = sql_showtable($table, true, $serveur_dest);
	}
	if (!$desc_dest) {
		spip_logger('dump')->error("Erreur creation '$table' sur serveur '$serveur_dest'" . var_export($desc, 1));
	}

	return $desc_dest;
}

/**
 * Copier de base a base
 *
 * @param string $status_file
 *   nom avec chemin complet du fichier ou est stocke le status courant
 * @param array $tables
 *   liste des tables a copier
 * @param string $serveur_source
 * @param string $serveur_dest
 * @param array $options
 *   parametres optionnels sous forme de tableau :
 *   param string $callback_progression
 *     fonction a appeler pour afficher la progression, avec les arguments (compteur,total,table)
 *   param int $max_time
 *     limite de temps au dela de laquelle sortir de la fonction proprement (de la forme time()+15)
 *   param bool $drop_source
 *     vider les tables sources apres copie
 *   param array $no_erase_dest
 *     liste des tables a ne pas vider systematiquement (ne seront videes que si existent dans la base source)
 *   param array $where
 *     liste optionnelle de condition where de selection des donnees pour chaque table
 *   param string $racine_fonctions_dest
 *     racine utilisee pour charger_fonction() des operations elementaires sur la base de destination.
 *     Permet de deleguer vers une autre voie de communication.
 *     Par defaut on utilise 'base', ce qui route vers les fonctions de ce fichier. Concerne :
 *     - vider_tables_destination_copie
 *     - preparer_table_dest
 *     - detruire_copieur_si_besoin
 *     - inserer_copie
 *   param array $fonction_base_inserer
 *     fonction d'insertion en base. Par defaut "inserer_copie" qui fait un insertq a l'identique.
 *     Attention, la fonction appelee est prefixee par $racine_fonctions_dest via un charger_fonction()
 *     Peut etre personalisee pour filtrer, renumeroter....
 *   param array $desc_tables_dest
 *     description des tables de destination a utiliser de preference a la description de la table source
 *   param int data_pool
 *     nombre de ko de donnees a envoyer d'un coup en insertion dans la table cible (par defaut 1)
 *     permet des envois groupes pour plus de rapidite, notamment si l'insertion est distante
 *
 * @return bool
 */
function base_copier_tables($status_file, $tables, $serveur_source, $serveur_dest, $options = []) {

	$status = [];
	$callback_progression = $options['callback_progression'] ?? '';
	$max_time = $options['max_time'] ?? 0;
	$drop_source = $options['drop_source'] ?? false;
	$no_erase_dest = $options['no_erase_dest'] ?? [];
	$where = $options['where'] ?? [];
	$fonction_base_inserer = $options['fonction_base_inserer'] ?? 'inserer_copie';
	$desc_tables_dest = $options['desc_tables_dest'] ?? [];
	$racine_fonctions = $options['racine_fonctions_dest'] ?? 'base';
	$data_pool = $options['data_pool'] ?? 50 * 1024;

	$logger = spip_logger('dump');

	$logger->notice(
		'Copier ' . count($tables) . " tables de '$serveur_source' vers '$serveur_dest'",
	);

	if (!$inserer_copie = charger_fonction($fonction_base_inserer, $racine_fonctions, true)) {
		$logger->notice("Fonction '{$racine_fonctions}_$fonction_base_inserer' inconnue. Abandon");

		return true; // echec mais on a fini, donc true
	}
	if (!$preparer_table_dest = charger_fonction('preparer_table_dest', $racine_fonctions, true)) {
		$logger->notice("Fonction '{$racine_fonctions}_$preparer_table_dest' inconnue. Abandon");

		return true; // echec mais on a fini, donc true
	}

	if (
		!lire_fichier($status_file, $status)
		|| !($status = unserialize($status))
	) {
		$status = [];
	}
	$status['etape'] = 'basecopie';

	// puis relister les tables a importer
	// et les vider si besoin, au moment du premier passage ici
	$initialisation_copie = $status['dump_status_copie'] ?? 0;

	// si init pas encore faite, vider les tables du serveur destination
	if (!$initialisation_copie) {
		if (
			!$vider_tables_destination_copie = charger_fonction(
				'vider_tables_destination_copie',
				$racine_fonctions,
				true
			)
		) {
			$logger->notice(
				"Fonction '{$racine_fonctions}_vider_tables_destination_copie' inconnue. Abandon",
			);

			return true; // echec mais on a fini, donc true
		}
		$vider_tables_destination_copie($tables, $no_erase_dest, $serveur_dest);
		$status['dump_status_copie'] = 'ok';
		ecrire_fichier($status_file, serialize($status));
	}

	// les tables auteurs et meta doivent etre copiees en dernier !
	if (in_array('spip_auteurs', $tables)) {
		$tables = array_diff($tables, ['spip_auteurs']);
		$tables[] = 'spip_auteurs';
	}
	if (in_array('spip_meta', $tables)) {
		$tables = array_diff($tables, ['spip_meta']);
		$tables[] = 'spip_meta';
	}
	$logger->info('Tables a copier :' . implode(', ', $tables));

	$trouver_table = charger_fonction('trouver_table', 'base');

	foreach ($tables as $table) {
		// si table commence par spip_ c'est une table SPIP, renommer le prefixe si besoin
		// sinon chercher la vraie table
		$desc_source = false;
		if (str_starts_with((string) $table, 'spip_')) {
			$desc_source = $trouver_table(preg_replace(',^spip_,', '', (string) $table), $serveur_source, true);
		}
		if (!$desc_source || !isset($desc_source['exist']) || !$desc_source['exist']) {
			$desc_source = $trouver_table($table, $serveur_source, false);
		}

		// verifier que la table est presente dans la base source
		if ($desc_source) {
			// $status['tables_copiees'][$table] contient l'avancement
			// de la copie pour la $table : 0 a N et -N quand elle est finie (-1 si vide et finie...)
			if (!isset($status['tables_copiees'][$table])) {
				$status['tables_copiees'][$table] = 0;
			}

			if (
				is_numeric($status['tables_copiees'][$table])
				&& $status['tables_copiees'][$table] >= 0
				&& ($desc_dest = $preparer_table_dest(
					$table,
					$desc_tables_dest[$table] ?? $desc_source,
					$serveur_dest,
					$status['tables_copiees'][$table] == 0
				))
			) {
				if ($callback_progression) {
					$callback_progression($status['tables_copiees'][$table], 0, $table);
				}
				while (true) {
					$n = (int) $status['tables_copiees'][$table];
					// on copie par lot de 400
					$res = sql_select(
						'*',
						$table,
						$where[$table] ?? '',
						'',
						'',
						"$n,400",
						'',
						$serveur_source
					);
					while ($row = sql_fetch($res, $serveur_source)) {
						$rows = [$row];
						// lire un groupe de donnees si demande en option
						// (permet un envoi par lot vers la destination)
						if ($data_pool > 0) {
							$s = strlen(serialize($row));
							while ($s < $data_pool && ($row = sql_fetch($res, $serveur_source))) {
								$s += strlen(serialize($row));
								$rows[] = $row;
							}
						}
						// si l'enregistrement est deja en base, ca fera un echec ou un doublon
						// mais si ca renvoie false c'est une erreur fatale => abandon
						if ($inserer_copie($table, $rows, $desc_dest, $serveur_dest) === false) {
							// forcer la sortie, charge a l'appelant de gerer l'echec
							$logger->error("Erreur fatale dans $inserer_copie table $table");
							$status['errors'][] = "Erreur fatale  lors de la copie de la table $table";
							ecrire_fichier($status_file, serialize($status));

							// copie finie
							return true;
						}
						$status['tables_copiees'][$table] += count($rows);
						if ($max_time && time() > $max_time) {
							break;
						}
					}
					if ($n == $status['tables_copiees'][$table]) {
						break;
					}
					$logger->notice("recopie $table " . $status['tables_copiees'][$table]);
					if ($callback_progression) {
						$callback_progression($status['tables_copiees'][$table], 0, $table);
					}
					ecrire_fichier($status_file, serialize($status));
					if ($max_time && time() > $max_time) {
						return false;
					} // on a pas fini, mais le temps imparti est ecoule
				}
				if ($drop_source) {
					sql_drop_table($table, '', $serveur_source);
					$logger->notice("drop $table sur serveur source '$serveur_source'");
				}
				$status['tables_copiees'][$table] = ($status['tables_copiees'][$table] ? -$status['tables_copiees'][$table] : 'zero');
				ecrire_fichier($status_file, serialize($status));
				$logger->info('tables_recopiees ' . implode(',', array_keys($status['tables_copiees'])));
				if ($callback_progression) {
					$callback_progression($status['tables_copiees'][$table], $status['tables_copiees'][$table], $table);
				}
			} else {
				if ($status['tables_copiees'][$table] < 0) {
					$logger->info("Table $table deja copiee : " . $status['tables_copiees'][$table]);
				}
				if ($callback_progression) {
					$callback_progression(
						0,
						$status['tables_copiees'][$table],
						"$table" . ((is_numeric($status['tables_copiees'][$table]) && $status['tables_copiees'][$table] >= 0) ? '[Echec]' : '')
					);
				}
			}
		} else {
			$status['errors'][] = "Impossible de lire la description de la table $table";
			ecrire_fichier($status_file, serialize($status));
			$logger->error("Impossible de lire la description de la table $table");
		}
	}

	// si le nombre de tables envoyees n'est pas egal au nombre de tables demandees
	// abandonner
	if ((is_countable($status['tables_copiees']) ? count($status['tables_copiees']) : 0) < count($tables)) {
		$logger->error(
			'Nombre de tables copiees incorrect : ' . (is_countable($status['tables_copiees']) ? count($status['tables_copiees']) : 0) . '/' . count($tables),
		);
		$status['errors'][] = 'Nombre de tables copiees incorrect : ' . (is_countable($status['tables_copiees']) ? count($status['tables_copiees']) : 0) . '/' . count($tables);
		ecrire_fichier($status_file, serialize($status));
	}

	if ($detruire_copieur_si_besoin = charger_fonction('detruire_copieur_si_besoin', $racine_fonctions, true)) {
		$detruire_copieur_si_besoin($serveur_dest);
	} else {
		$logger->notice("Fonction '{$racine_fonctions}_detruire_copieur_si_besoin' inconnue.");
	}

	// OK, copie complete
	return true;
}

/**
 * fonction d'insertion en base lors de la copie de base a base
 *
 * @param string $table
 * @param array $rows
 * @param array $desc_dest
 * @param string $serveur_dest
 * @return int/bool
 */
function base_inserer_copie($table, $rows, $desc_dest, $serveur_dest) {

	// verifier le nombre d'insertion
	$nb1 = sql_countsel($table, '', '', '', $serveur_dest);
	// si l'enregistrement est deja en base, ca fera un echec ou un doublon
	$r = sql_insertq_multi($table, $rows, $desc_dest, $serveur_dest);
	$nb = sql_countsel($table, '', '', '', $serveur_dest);
	if ($nb - $nb1 < count($rows)) {
		spip_logger('dump')->notice(
			'base_inserer_copie : ' . ($nb - $nb1) . ' insertions au lieu de ' . count($rows) . '. On retente 1 par 1',
		);
		foreach ($rows as $row) {
			// si l'enregistrement est deja en base, ca fera un echec ou un doublon
			$r = sql_insertq($table, $row, $desc_dest, $serveur_dest);
		}
		// on reverifie le total
		$r = 0;
		$nb = sql_countsel($table, '', '', '', $serveur_dest);
		if ($nb - $nb1 < count($rows)) {
			spip_logger('dump')->error(
				'base_inserer_copie : ' . ($nb - $nb1) . ' insertions au lieu de ' . count($rows) . ' apres insertion 1 par 1',
			);
			$r = false;
		}
	}

	return $r;
}
