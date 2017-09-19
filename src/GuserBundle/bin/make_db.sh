#!/bin/sh

APPBASE=$(readlink -f "$(dirname $0)/../../../")

php bin/console doctrine:fixtures:load --append --fixtures="${APPBASE}/src/GuserBundle/DataFixtures"

