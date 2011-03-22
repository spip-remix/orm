<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) return;

// Programme de mise a jour des tables SQL lors d'un chgt de version.
// Marche aussi pour les plugins en appelant directement la fonction maj_while.
// Pour que ceux-ci profitent aussi de la reprise sur interruption,
// ils doivent indiquer leur numero de version installee dans une meta.
// Le nom de cette meta doit aussi etre un tableau global
// dont l'index "maj" est le sous-tableau des mises a jours
// et l'index "cible" la version a atteindre
// A tester.

// http://doc.spip.org/@base_upgrade_dist
function base_upgrade_dist($titre='', $reprise='')
{
	if (!$titre) return; // anti-testeur automatique
	if ($GLOBALS['spip_version']!=$GLOBALS['meta']['version_installee']) {
		if (!is_numeric(_request('reinstall'))) {
			include_spip('base/create');
			spip_log("recree les tables eventuellement disparues","maj."._LOG_INFO_IMPORTANTE);
			creer_base();
		}
		// securisons les variables d'upgrade
		$meta = preg_replace('[^\w]','',_request('meta'));
		$table = preg_replace('[^\w]','',_request('table'));
		if (!$meta)
			// lancement initial de l'upgrade
			$res = maj_base();
		else
			// reprise sur demande de mise a jour interrompue pour plugin
			$res= maj_while($GLOBALS[$table][$meta],
				  $GLOBALS[$meta]['cible'],
				  $GLOBALS[$meta]['maj'],
				  $meta,
				  $table);
		if ($res) {
			if (!is_array($res))
				spip_log("Pb d'acces SQL a la mise a jour","maj."._LOG_INFO_ERREUR);
			else {
				include_spip('inc/minipres');
				echo minipres(_T('avis_operation_echec') . ' ' . join(' ', $res));
				exit;
			}
		}
	}
	spip_log("Fin de mise a jour SQL. Debut m-a-j acces et config","maj."._LOG_INFO_IMPORTANTE);
	
	// supprimer quelques fichiers temporaires qui peuvent se retrouver invalides
	spip_unlink(_DIR_TMP.'plugin_xml.cache');
	spip_unlink(_DIR_SESSIONS.'ajax_fonctions.txt');
	spip_unlink(_CACHE_PIPELINES);
	spip_unlink(_CACHE_RUBRIQUES);
	spip_unlink(_CACHE_PLUGINS_OPT);
	spip_unlink(_CACHE_PLUGINS_FCT);
	spip_unlink(_CACHE_PLUGINS_VERIF);

	include_spip('inc/auth');
	auth_synchroniser_distant();
	$config = charger_fonction('config', 'inc');
	$config();
}

// http://doc.spip.org/@maj_base
function maj_base($version_cible = 0) {
	global $spip_version_base;

	$version_installee = @$GLOBALS['meta']['version_installee'];
	//
	// Si version nulle ou inexistante, c'est une nouvelle installation
	//   => ne pas passer par le processus de mise a jour.
	// De meme en cas de version superieure: ca devait etre un test,
	// il y a eu le message d'avertissement il doit savoir ce qu'il fait
	//
	// version_installee = 1.702; quand on a besoin de forcer une MAJ
	
	spip_log("Version anterieure: $version_installee. Courante: $spip_version_base","maj."._LOG_INFO_IMPORTANTE);
	if (!$version_installee OR ($spip_version_base < $version_installee)) {
		sql_replace('spip_meta', 
		      array('nom' => 'version_installee',
			    'valeur' => $spip_version_base,
			    'impt' => 'non'));
		return false;
	}
	if (!upgrade_test()) return true;
	
	$cible = ($version_cible ? $version_cible : $spip_version_base);

	if ($version_installee <= 1.926) {
		$n = floor($version_installee * 10);
		while ($n < 19) {
			$nom  = sprintf("v%03d",$n);
			$f = charger_fonction($nom, 'maj', true);
			if ($f) {
				spip_log( "$f repercute les modifications de la version " . ($n/10),"maj."._LOG_INFO_IMPORTANTE);
				$f($version_installee, $spip_version_base);
			} else spip_log( "pas de fonction pour la maj $n $nom","maj."._LOG_INFO_IMPORTANTE);
			$n++;
		}
		include_spip('maj/v019_pre193');
		v019_pre193($version_installee, $version_cible);
	}
	if ($version_installee < 2000) {
		if ($version_installee < 2)
			$version_installee = $version_installee*1000;
		include_spip('maj/v019');
	}
	if ($cible < 2)
		$cible = $cible*1000;

	include_spip('maj/svn10000');
	return maj_while($version_installee, $cible, $GLOBALS['maj'], 'version_installee');
}

// A partir des > 1.926 (i.e SPIP > 1.9.2), cette fonction gere les MAJ.
// Se relancer soi-meme pour eviter l'interruption pendant une operation SQL
// (qu'on espere pas trop longue chacune)
// evidemment en ecrivant dans la meta a quel numero on en est.
// Cette fonction peut servir aux plugins qui doivent donner comme arguments:
// 1. le numero de version courant (nombre entier; ex: numero de commit)
// 2. le numero de version a atteindre (idem)
// 3. le tableau des instructions de mise a jour a executer
// Pour profiter du mecanisme de reprise sur interruption il faut de plus
// 4. le nom de la meta permettant de retrouver tout ca
// 5. la table des meta ou elle se trouve ($table_prefix . '_meta' par defaut)
// (cf debut de fichier)
// en cas d'echec, cette fonction retourne un tableau (etape,sous-etape)
// sinon elle retourne un tableau vide

define('_UPGRADE_TIME_OUT', 20);

function relance_maj($meta,$table){
	$installee = $GLOBALS[$table][$meta];
	if (!headers_sent())
		redirige_url_ecrire('upgrade', "reinstall=$installee&meta=$meta&table=$table");
	else {
		$redirect = generer_url_ecrire('upgrade',"reinstall=$installee&meta=$meta&table=$table",true);
		echo http_script("location.href=\"".$redirect."\";");
	}
}

function maj_debut_page($installee,$meta,$table){
	static $done = false;
	if ($done) return;
	include_spip('inc/minipres');
	@ini_set("zlib.output_compression","0"); // pour permettre l'affichage au fur et a mesure
	$timeout = _UPGRADE_TIME_OUT*2;
	$titre = _T('titre_page_upgrade');
	$balise_img = chercher_filtre('balise_img');
	$titre .= $balise_img(chemin_image('searching.gif'));
	echo ( install_debut_html($titre));
	// script de rechargement auto sur timeout
	$redirect = generer_url_ecrire('upgrade',"reinstall=$installee&meta=$meta&table=$table",true);
	echo http_script("window.setTimeout('location.href=\"".$redirect."\";',".($timeout*1000).")");
	echo "<div style='text-align: left'>\n";
	ob_flush();flush();
}

// http://doc.spip.org/@maj_while
function maj_while($installee, $cible, $maj, $meta='', $table='meta')
{
	$n = 0;
	$time = time();
	// definir le timeout qui peut etre utilise dans les fonctions
	// de maj qui durent trop longtemps
	define('_TIME_OUT',$time+_UPGRADE_TIME_OUT);

	while ($installee < $cible) {
		$installee++;
		// si une maj pour cette version
		if (isset($maj[$installee])) {
			maj_debut_page($installee,$meta,$table);
			echo $installee;
			$etape = serie_alter($installee, $maj[$installee], $meta, $table);
			
			if ($etape) return array($installee, $etape);
			$n = time() - $time;
			spip_log( "$table $meta: $installee en $n secondes",'maj.'._LOG_INFO_IMPORTANTE);
			if ($meta) ecrire_meta($meta, $installee,'non', $table);
			echo "<br />";
		}
		if (time() >= _TIME_OUT) {
			relance_maj($meta,$table);
		}
	}
	// indispensable pour les chgt de versions qui n'ecrivent pas en base
	// tant pis pour la redondance eventuelle avec ci-dessus
	if ($meta) ecrire_meta($meta, $installee,'non');
	spip_log( "MAJ terminee. $meta: $installee",'maj.'._LOG_INFO_IMPORTANTE);
	return array();
}

// Appliquer une serie de chgt qui risquent de partir en timeout
// (Alter cree une copie temporaire d'une table, c'est lourd)

// http://doc.spip.org/@serie_alter
function serie_alter($serie, $q = array(), $meta='', $table='meta') {
	$meta .= '_maj_' . $serie;
	$etape = intval(@$GLOBALS[$table][$meta]);
	foreach ($q as $i => $r) {
		if ($i >= $etape) {
			$msg = "maj $table $meta etape $i";
			if (is_array($r)
			  AND function_exists($f = array_shift($r))) {
				spip_log( "$msg: $f " . join(',',$r),'maj.'._LOG_INFO_IMPORTANTE);
				// pour les fonctions atomiques sql_xx
				// on enregistre le meta avant de lancer la fonction,
				// de maniere a eviter de boucler sur timeout
				// mais pour les fonctions complexes,
				// il faut les rejouer jusqu'a achevement.
				// C'est a elle d'assurer qu'elles progressent a chaque rappel
				if (strncmp($f,"sql_",4)==0)
					ecrire_meta($meta, $i+1, 'non', $table);
				echo " . $i";
				call_user_func_array($f, $r);
				// si temps imparti depasse, on relance sans ecrire en meta
				// car on est peut etre sorti sur timeout si c'est une fonction longue
				if (time() >= _TIME_OUT) {
					relance_maj($meta,$table);
				}
				ecrire_meta($meta, $i+1, 'non', $table);
				spip_log( "$meta: ok", 'maj.'._LOG_INFO_IMPORTANTE);
			}
			else
				return $i+1;
		}
	}
	effacer_meta($meta, $table);
	return 0;
}



// La fonction a appeler dans le tableau global $maj 
// quand on rajoute des types MIME. cf par exemple la 1.953

// http://doc.spip.org/@upgrade_types_documents
function upgrade_types_documents() {
	if (include_spip('base/medias')
	  AND function_exists('creer_base_types_doc'));
		creer_base_types_doc();
}

// http://doc.spip.org/@upgrade_test
function upgrade_test() {
	sql_drop_table("spip_test", true);
	sql_create("spip_test", array('a' => 'int'));
	sql_alter("TABLE spip_test ADD b INT");
	sql_insertq('spip_test', array('b' => 1), array('field'=>array('b' => 'int')));
	$result = sql_select('b', "spip_test");
	// ne pas garder le resultat de la requete sinon sqlite3 
	// ne peut pas supprimer la table spip_test lors du sql_alter qui suit
	// car cette table serait alors 'verouillee'
	$result = $result?true:false; 
	sql_alter("TABLE spip_test DROP b");
	return $result;
}

// pour versions <= 1.926
// http://doc.spip.org/@maj_version
function maj_version ($version, $test = true) {
	if ($test) {
		if ($version>=1.922)
			ecrire_meta('version_installee', $version, 'non');
		else {
			// on le fait manuellement, car ecrire_meta utilise le champs impt qui est absent sur les vieilles versions
			$GLOBALS['meta']['version_installee'] = $version;
			sql_updateq('spip_meta',  array('valeur' => $version), "nom=" . sql_quote('version_installee') );
		}
		spip_log( "mise a jour de la base en $version","maj."._LOG_INFO_IMPORTANTE);
	} else {
		echo _T('alerte_maj_impossible', array('version' => $version));
		exit;
	}
}

// pour versions <= 1.926
// http://doc.spip.org/@upgrade_vers
function upgrade_vers($version, $version_installee, $version_cible = 0){
	return ($version_installee<$version
		AND (($version_cible>=$version) OR ($version_cible==0))
	);
}
?>
