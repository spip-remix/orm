# Connecteurs

## Configuration

```php
// config/orm.php

return [
    'spip' => [                             // Nom de la configuration
        'driver' => 'mysqli',               // Driver de connexion (parmis pdo_sqlite, mysqli, pgsql, pdo_mysql, pdo_pgsql, ...)
        'parameters' => [
            'table_prefix' => 'spip',       // Préfixe des tables SQL
            'hostname' => 'localhost',      // Hôte à joindre
            // 'port' => 3306,                 // Port TCP le l'hôte pour accéder au serveur SQL
            'base' => 'spip',               // Nom du schéma SQL, de la base
            'username' => 'spip',           // User SQL d'authentification au serveur pour l'utilisation des données
            'password' => 'spip',           // Password SQL d'authentification au serveur pour l'utilisation des données
            // 'alter_username' => 'root',  // User SQL d'authentification facultatif pour l'alteration du schéma/base
            // 'alter_password' => '',      // Password SQL d'authentification facultatif pour l'alteration du schéma/base
        ],
    ],
    'autre_base' => [
        'driver' => 'pdo_mysql',
        'parameters' => [
            'table_prefix' => 'grmleu',
            'hostname' => 'localhost',
            'base' => 'spip',
            'username' => 'spip',
            'password' => 'spip',
        ],
    ],
    'socket_spip' => [
        'driver' => 'pdo_mysql',
        'parameters' => [
            'table_prefix' => 'grmleu',
            'socket' => '/tmp/autre_spip.sock',
            'base' => 'spip',
            'username' => 'spip',
            'password' => 'spip',
        ],
    ],
    'externe' => [
        'driver' => 'pdo_sqlite',
        'parameters' => [
            'table_prefix' => '',
            'filename' => '/opt/data/base.sqlite.db',
        ],
    ],
];
```
