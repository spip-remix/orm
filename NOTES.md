# Reprise

## API

## architeccture

ecrire/base :

- abstract_sql.php
- objets.php
- connect_sql.php

abstract_sql::sql_serveur -> connect_sql::spip_connect_sql -> connect_sql::spip_connect -> connect_sql::spip_connect_main

parametres : $serveur, $ins_sql (instruction: SELECT,UPDATE, ...)

globales : $GLOBALS['spip_sql_version'],  $GLOBALS['connexions'][$index], $GLOBALS['db_ok'], $GLOBALS['spip_connect_version']
$GLOBALS['tables_principales']

constantes  : _DIR_CONNECT, _FILE_CONNECT, _FILE_CONNECT_TMP -> config/connect.php _ECRIRE_INSTALL _PLUGINS_HASH  _VAR_MODE _REDIRECT_MAJ_PLUGIN _TIME_OUT

SQL_ABSTRACT_VERSION
_CONNECT_RETRY_DELAY
_UPGRADE_TIME_OUT

cms=articles,auteurs,rubriques,jobs
