php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

export DATABASE="stagtvp"
sh src/StagBundle/bin/make_db.sh
sh src/GuserBundle/bin/make_db.sh

