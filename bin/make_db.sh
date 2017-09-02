php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

export DATABASE="$(cat app/config/parameters.yml | grep database_name | awk '{print $2}')"
export DPASSWORD="$(cat app/config/parameters.yml | grep database_password | awk -F"'" '{print $2}')"
export MYSQL="mysql -h 172.17.0.3 -u $DATABASE -p$DPASSWORD"

sh src/StagBundle/bin/make_db.sh
sh src/GuserBundle/bin/make_db.sh

