#!/bin/sh

php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:clear --env=dev --no-warmup

