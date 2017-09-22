#!/bin/sh

DATABASE="$(cat app/config/parameters.yml | grep database_name | awk '{print $2}')"
PASSWORD="$(cat app/config/parameters.yml | grep database_password | awk '{print $2}')"
HOST="$(cat app/config/parameters.yml | grep database_host | awk '{print $2}')"

mysqldump -h${HOST} -u${DATABASE} -p${PASSWORD} ${DATABASE} --all-tablespaces --skip-lock-tables > database-backup-$(date +%Y%m%d%H%M%S).sql
