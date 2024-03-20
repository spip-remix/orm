# Accès aux bases de données SQL

- [x] : import fichier historique
- [x] : composerisation
- [x] : pluginisation
- [ ] : Producteur de reqête SQL (Buiilder)
- [ ] : Connecteurs "réseaux" TCP/Socket UNIX/Fichier/Autres

## Fichiers historiques

- `ecrire/base`
- `ecrire/req`
- `ecrire/src/Sql`
- appels `inc/xxx`:
  - spip/spip:
  - plugins-dist:
  - contributions:

## Dépendances

### Constantes

### Globales

### Metas

### Config

### Fonctions

## Récup historique git

```bash
git clone --single-branch --no-tags git@git.spip.net:spip/spip.git orm
cd orm
git filter-repo \
  --path ecrire/base \
  --path ecrire/req \
  --path ecrire/src/Sql \
  --path-rename ecrire/base:base \
  --path-rename ecrire/req:req \
  --path-rename ecrire/src/Sql:src \
  --force
git branch -m 5.0
```

## composer

### Extensions

- ext-sqlite3
ext-sockets

ext-ldap                 8.3.3    The ldap PHP extension
ext-mysqli               8.3.3    The mysqli PHP extension
ext-mysqlnd              0        The mysqlnd PHP extension (actual version: mysqlnd 8.3.3)
ext-odbc                 8.3.3    The odbc PHP extension
ext-openssl              8.3.3    The openssl PHP extension
ext-pdo                  8.3.3    The PDO PHP extension
ext-pdo_mysql            8.3.3    The pdo_mysql PHP extension
ext-pdo_odbc             8.3.3    The PDO_ODBC PHP extension
ext-pdo_pgsql            8.3.3    The pdo_pgsql PHP extension
ext-pdo_sqlite           8.3.3    The pdo_sqlite PHP extension
ext-pgsql                8.3.3    The pgsql PHP extension

lib-ldap-openldap        2.6.7    OpenLDAP version of ldap
lib-openssl              3.2.1    OpenSSL 3.2.1 30 Jan 2024
lib-pdo_pgsql-libpq      16.2     libpq for pdo_pgsql
lib-pdo_sqlite-sqlite    3.45.1   The pdo_sqlite-sqlite library
lib-pgsql-libpq          16.2     libpq for pgsql
lib-sqlite3-sqlite       3.45.1   The sqlite3-sqlite library
php-ipv6                 8.1.27   Package overridden via config.platform, actual: 8.3.3
