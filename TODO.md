# Accès aux bases de données SQL

- [x]: import fichier historique
- [ ]: composerisation

## Fichiers historiques

- `ecrire/base`
- `ecrire/req`
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
