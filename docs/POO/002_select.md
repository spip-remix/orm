# select

```php
use SpipRemix\Component\Orm\SqlQueryBuilder;

$req = new SqlQueryBuilder;

$req = $sql->select('meta');
// SELECT * FROM spip_meta;

$req = $sql->select('meta', prefix:'externe');
// SELECT * FROM externe_meta;

$req = $sql->select('meta', ['nom', 'valeur']);
$req = $sql->select('meta', select:['nom', 'valeur']);
// SELECT nom, valeur FROM spip_meta;

$req = $sql->select('meta', 'MAX(maj) AS last_modfied');
// SELECT MAX(maj) AS last_modfied FROM spip_meta;

$req = $sql->select('meta', ['nom', 'valeur'], limit:10);
// SELECT nom, valeur FROM spip_meta LIMIT 10;

$req = $sql->select('meta', ['valeur'], ['nom=\'charset\'']);
$req = $sql->select('meta', ['valeur'], where:['nom=\'charset\'']);
$req = $sql->select('meta', where:['nom=\'charset\''], select:['valeur']);
// SELECT valeur FROM spip_meta WHERE nom='charset';

$req = $sql->select(select:['valeur1', 'valeur2'], from:['table1', 'table2'], where:['nom1=\'indicateur1\'']);
// SELECT valeur1, valeur2 FROM spip_table1, spip_table2 WHERE nom1='indicateur1';
$req = $sql->select(select:['t1.valeur1 AS valeur1', 'valeur2'], from:['table1 AS t1', 'table2'], where:['t1.nom1=\'indicateur1\'']);
// SELECT t1.valeur1 AS valeur1, valeur2 FROM spip_table1 AS t1, spip_table2 WHERE t1.nom1='indicateur1';
```
