<?php

use Psr\Log\LogLevel;

/***************************************************************************\
 *  SPIP, Système de publication pour l'internet                           *
 *                                                                         *
 *  Copyright © avec tendresse depuis 2001                                 *
 *  Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribué sous licence GNU/GPL.     *
\***************************************************************************/

/**
 * Gestion de l'obtention des descriptions de tables SQL
 *
 * @package SPIP\Core\SQL\Tables
 **/
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}
include_spip('base/objets');

/**
 * Retourne la description d'une table SQL
 *
 * Cela sert notamment au moment de la compilation des boucles, critères et balise.
 *
 * Les champs et clés de la tables sont retrouvés prioritairement via le
 * gestionnaire de base de données. Les descriptions sont complétées,
 * pour les tables éditoriales, des informations déclarées ou construites
 * par la déclaration des objets éditoriaux.
 *
 * @example
 *     $trouver_table = charger_fonction('trouver_table', 'base');
 *     $desc = $trouver_table('spip_groupes_mots');
 *
 * Cette fonction intervient à la compilation, mais aussi pour la balise
 * contextuelle EXPOSE ou certains critères.
 *
 * L'ensemble des descriptions de table d'un serveur est stocké dans un
 * fichier cache/sql_desc.txt par soucis de performance. Un appel
 * avec $nom vide est une demande explicite de vidange de ce cache
 *
 * @see lister_tables_objets_sql()
 *
 * @api
 * @param string $nom
 *     Nom de la table
 *     Vide '' demande de vider le cache des discriptions
 * @param string $serveur
 *     Nom du connecteur
 * @param bool $table_spip
 *     Indique s'il faut transformer le préfixe de table
 * @param array $options
 *     - bool log_missing:
 *           true: (par défaut) pour écrire un log en cas de table absente.
 *           false: log uniquement en niveau debug.
 * @return array|null
 *     null si pas de connexion sql ou table introuvable
 *     tableau de description de la table sinon, en particulier :
 *     - field : tableau des colonnes SQL et leur description (comme dans serial.php ou objets.php)
 *     - key   : tableau des KEY (comme dans serial.php ou objets.php)
 *     - table et table_sql : nom de la table (avec spip_ en préfixe)
 *     - id_table : nom SPIP de la table (type de boucle)
 *                  le compilateur produit  FROM $r['table'] AS $r['id_table']
 *     - Toutes les autres informations des objets éditoriaux si la table est l'un d'eux.
 *
 *
 **/
function base_trouver_table_dist($nom, $serveur = '', $table_spip = true, array $options = []) {
	$desc_cache = null;
	static $nom_cache_desc_sql = [];

	if (
		!spip_connect($serveur)
		|| !preg_match('/^[a-zA-Z0-9._-]*/', $nom)
	) {
		return null;
	}

	$options += [
		// si false, baissera le niveau de log si une table demandée n’existe pas
		'log_missing' => true,
	];

	$connexion = &$GLOBALS['connexions'][$serveur ? strtolower($serveur) : 0];
	$objets_sql = lister_tables_objets_sql('::md5');

	// le nom du cache depend du serveur mais aussi du nom de la db et du prefixe
	// ce qui permet une auto invalidation en cas de modif manuelle du fichier
	// de connexion, et tout risque d'ambiguite
	if (!isset($nom_cache_desc_sql[$serveur][$objets_sql])) {
		$nom_cache_desc_sql[$serveur][$objets_sql] =
			_DIR_CACHE . 'sql_desc_'
			. ($serveur ? "{$serveur}_" : '')
			. substr(md5($connexion['db'] . ':' . $connexion['prefixe'] . ":$objets_sql"), 0, 8)
			. '.txt';
		// nouveau nom de cache = nouvelle version en memoire
		unset($connexion['tables']);
	}

	// un appel avec $nom vide est une demande explicite de vidange du cache des descriptions
	if (!$nom) {
		spip_unlink($nom_cache_desc_sql[$serveur][$objets_sql]);
		$connexion['tables'] = [];

		return null;
	}

	$nom_sql = $nom;
	$nom_sql = preg_match('/\.(.*)$/', $nom, $s) ? $s[1] : $nom;

	$fdesc = $desc = '';
	$connexion = &$GLOBALS['connexions'][$serveur ?: 0];

	// base sous SPIP: gerer les abreviations explicites des noms de table
	if (
		$connexion['spip_connect_version']
		&& $table_spip
		&& isset($GLOBALS['table_des_tables'][$nom])
	) {
		$nom = $GLOBALS['table_des_tables'][$nom];
		$nom_sql = 'spip_' . $nom;
	}

	// si c'est la premiere table qu'on cherche
	// et si on est pas explicitement en recalcul
	// on essaye de recharger le cache des decriptions de ce serveur
	// dans le fichier cache
	if (
		!isset($connexion['tables'][$nom_sql])
		&& defined('_VAR_MODE')
		&& _VAR_MODE !== 'recalcul'
		&& (!isset($connexion['tables']) || !$connexion['tables'])
		&& lire_fichier($nom_cache_desc_sql[$serveur][$objets_sql], $desc_cache)
		&& ($desc_cache = unserialize($desc_cache))
	) {
		$connexion['tables'] = $desc_cache;
	}
	if ($table_spip && !isset($connexion['tables'][$nom_sql])) {
		if (isset($GLOBALS['tables_principales'][$nom_sql])) {
			$fdesc = $GLOBALS['tables_principales'][$nom_sql];
		}
		// meme si pas d'abreviation declaree, trouver la table spip_$nom
		// si c'est une table principale,
		// puisqu'on le fait aussi pour les tables auxiliaires
		elseif ($nom_sql == $nom && isset($GLOBALS['tables_principales']['spip_' . $nom])) {
			$nom_sql = 'spip_' . $nom;
			$fdesc = &$GLOBALS['tables_principales'][$nom_sql];
		} elseif (
			isset($GLOBALS['tables_auxiliaires'][$n = $nom])
			|| isset($GLOBALS['tables_auxiliaires'][$n = 'spip_' . $nom])
		) {
			$nom_sql = $n;
			$fdesc = &$GLOBALS['tables_auxiliaires'][$n];
		}  # table locale a cote de SPIP, comme non SPIP:
	}
	if (!isset($connexion['tables'][$nom_sql])) {
		// La *vraie* base a la priorite
		$exists = sql_table_exists($nom_sql, $table_spip, $serveur);
		if (
			!$exists
			|| !($desc = sql_showtable($nom_sql, $table_spip, $serveur))
			|| !$desc['field']
		) {
			if (!$fdesc) {
				spip_logger('base')->log(
					$options['log_missing'] ? LogLevel::NOTICE : LogLevel::DEBUG,
					"trouver_table: table inconnue '$serveur' '$nom'"
				);

				return null;
			}
			// on ne sait pas lire la structure de la table :
			// on retombe sur la description donnee dans les fichiers spip
			$desc = $fdesc;
			$desc['exist'] = false;
		} else {
			$desc['exist'] = true;
			// gerer le cas des cles vides (echec de l'analyse sur une vue par exemple)
			// pour recuperer la declaration de lister_tables_objets_sql() si il y en a une
			if (! $desc['key']) {
				spip_logger('base')->info("trouver_table: table sans cle '$serveur' '$nom'");
				unset($desc['key']);
			}
		}

		$desc['table'] = $desc['table_sql'] = $nom_sql;
		$desc['connexion'] = $serveur;

		// charger les infos declarees pour cette table
		// en lui passant les infos connues
		// $desc est prioritaire pour la description de la table
		$desc = array_merge(lister_tables_objets_sql($nom_sql, $desc), $desc);
		// s'assurer qu'on a toujours un 'key'
		if (!isset($desc['key']) && !empty($fdesc['key'])) {
			$desc['key'] = $fdesc['key'];
		}
		if (! isset($desc['key'])) {
			$desc['key'] = [];
		}

		// si tables_objets_sql est bien fini d'init, on peut cacher
		$connexion['tables'][$nom_sql] = $desc;
		$res = &$connexion['tables'][$nom_sql];
		// une nouvelle table a ete decrite
		// mettons donc a jour le cache des descriptions de ce serveur
		if (is_writable(_DIR_CACHE)) {
			ecrire_fichier($nom_cache_desc_sql[$serveur][$objets_sql], serialize($connexion['tables']), true);
		}
	} else {
		$res = &$connexion['tables'][$nom_sql];
	}

	// toujours retourner $nom dans id_table
	$res['id_table'] = $nom;

	return $res;
}
