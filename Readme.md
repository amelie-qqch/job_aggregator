# Installation du projet
- Vérifier que vous êtes bien en posséssion du fichier .env.dev et que celui-ci est à la racine du projet
- Lancer le conteneur avec la commande suivante :
` docker compose --env-file .env.dev up -d`
 Cette commande s'occupera d'installer les vendeurs ainsi que d'initialiser la base de données.

# Utiliser le projet

Vous pouvez utiliser la commande `app:fetch-jobs` qui récupèrera les offres d'emploi de l'API France Travail.

Pour cela dans un premier temps connectez vous au conteneur :
```
docker exec -ti job_aggregator bash
```
Puis lancer la commande suivante (avec `--help` pour voir les options à votre disposition):
```
bin/console app:fetch-jobs
```
