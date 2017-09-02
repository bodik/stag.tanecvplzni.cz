#!/bin/sh

export DATABASE="$(cat app/config/parameters.yml | grep database_name | awk '{print $2}')"
export DPASSWORD="$(cat app/config/parameters.yml | grep database_password | awk -F"'" '{print $2}')"
export MYSQL="mysql -u $DATABASE -p$DPASSWORD"

mysqldump -u$DATABASE -p$DPASSWORD $DATABASE --all-tablespaces --skip-lock-tables > database-backup-$(date +%Y%m%d%H%M%S).sql
