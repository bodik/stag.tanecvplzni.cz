#!/bin/sh

APPBASE=$(readlink -f "$(dirname $0)/../../../")

rm -r ${APPBASE}/var/data-stagbundle-blob
php bin/console doctrine:fixtures:load --append --fixtures="${APPBASE}/src/StagBundle/DataFixtures"
chown -R www-data ${APPBASE}/var/data-stagbundle-blob

