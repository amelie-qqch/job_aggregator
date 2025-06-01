#!/bin/bash

echo pwd
composer install
# S'il n'y a pas de fichier .db dans le dossier var on lance la création de la base de données
if [ ! -f var/.db ]; then
    php bin/console doctrine:database:create
fi

# On lance les migrations pour mettre à jour la base de données
php bin/console doctrine:migrations:migrate --no-interaction
