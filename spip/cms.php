<?php

use Spip\Component\Database\Repositories;
use Spip\Component\Database\Repository;
use Spip\Component\Database\Repository\MySqlConnector;
use Spip\Component\Database\RepositoryInterface;
use Spip\Component\Database\Table;
use Spip\Component\Database\Schema;

require_once __DIR__ . '/../vendor/autoload.php';

$articles = new Table([
    'id_article' => 'bigint(21) NOT NULL',
    'surtitre' => "text DEFAULT '' NOT NULL",
    'titre' => "text DEFAULT '' NOT NULL",
    'soustitre' => "text DEFAULT '' NOT NULL",
    'id_rubrique' => "bigint(21) DEFAULT '0' NOT NULL",
    'descriptif' => "text DEFAULT '' NOT NULL",
    'chapo' => "mediumtext DEFAULT '' NOT NULL",
    'texte' => "longtext DEFAULT '' NOT NULL",
    'ps' => "mediumtext DEFAULT '' NOT NULL",
    'date' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'statut' => "varchar(10) DEFAULT '0' NOT NULL",
    'id_secteur' => "bigint(21) DEFAULT '0' NOT NULL",
    'maj' => 'TIMESTAMP',
    'export' => "VARCHAR(10) DEFAULT 'oui'",
    'date_redac' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'visites' => "integer DEFAULT '0' NOT NULL",
    'referers' => "integer DEFAULT '0' NOT NULL",
    'popularite' => "DOUBLE DEFAULT '0' NOT NULL",
    'accepter_forum' => "CHAR(3) DEFAULT '' NOT NULL",
    'date_modif' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'lang' => "VARCHAR(10) DEFAULT '' NOT NULL",
    'langue_choisie' => "VARCHAR(3) DEFAULT 'non'",
    'id_trad' => "bigint(21) DEFAULT '0' NOT NULL",
    'nom_site' => "tinytext DEFAULT '' NOT NULL",
    'url_site' => "text DEFAULT '' NOT NULL",
    'virtuel' => "text DEFAULT '' NOT NULL",
]);
$auteurs = new Table([
    'id_auteur' => 'bigint(21) NOT NULL',
    'nom' => "text DEFAULT '' NOT NULL",
    'bio' => "text DEFAULT '' NOT NULL",
    'email' => "tinytext DEFAULT '' NOT NULL",
    'nom_site' => "tinytext DEFAULT '' NOT NULL",
    'url_site' => "text DEFAULT '' NOT NULL",
    'login' => 'VARCHAR(255) BINARY',
    'pass' => "tinytext DEFAULT '' NOT NULL",
    'low_sec' => "tinytext DEFAULT '' NOT NULL",
    'statut' => "varchar(255)  DEFAULT '0' NOT NULL",
    'webmestre' => "varchar(3)  DEFAULT 'non' NOT NULL",
    'maj' => 'TIMESTAMP',
    'pgp' => "TEXT DEFAULT '' NOT NULL",
    'htpass' => "tinytext DEFAULT '' NOT NULL",
    'en_ligne' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'alea_actuel' => 'tinytext',
    'alea_futur' => 'tinytext',
    'prefs' => 'text',
    'cookie_oubli' => 'tinytext',
    'source' => "VARCHAR(10) DEFAULT 'spip' NOT NULL",
    'lang' => "VARCHAR(10) DEFAULT '' NOT NULL",
    'imessage' => "VARCHAR(3) DEFAULT '' NOT NULL",
    'backup_cles' => "mediumtext DEFAULT '' NOT NULL",
]);
$rubriques = new Table([
    'id_rubrique' => 'bigint(21) NOT NULL',
    'id_parent' => "bigint(21) DEFAULT '0' NOT NULL",
    'titre' => "text DEFAULT '' NOT NULL",
    'descriptif' => "text DEFAULT '' NOT NULL",
    'texte' => "longtext DEFAULT '' NOT NULL",
    'id_secteur' => "bigint(21) DEFAULT '0' NOT NULL",
    'maj' => 'TIMESTAMP',
    'statut' => "varchar(10) DEFAULT '0' NOT NULL",
    'date' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'lang' => "VARCHAR(10) DEFAULT '' NOT NULL",
    'langue_choisie' => "VARCHAR(3) DEFAULT 'non'",
    'statut_tmp' => "varchar(10) DEFAULT '0' NOT NULL",
    'date_tmp' => "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
    'profondeur' => "smallint(5) DEFAULT '0' NOT NULL"
]);

$jobs = new Table();

$infos = new Schema();

$infos['spip_articles'] = $articles;
$infos['spip_rubriques'] = $rubriques;
$infos['spip_auteurs'] = $auteurs;
$infos['spip_jobs'] =  $jobs;

// dump($infos->all());

$serveur = new Repository('', new MySqlConnector);
$serveurs = new Repositories;
$serveurs->add($serveur);

function sql_truc(string|RepositoryInterface $serveur, $data)
{
    global $serveurs;
    if (is_string($serveur)) {
        $serveur = $serveurs->get($serveur);
    }
    if (!$serveur instanceof RepositoryInterface) {
        throw new Exception("Error Processing Request", 1);
    }

    $test = $serveur->select($data);
    return $data;
}

dump(sql_truc('', ['test'=> 'data']));
dump(sql_truc($serveur, ['test'=> 'data']));