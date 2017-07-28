#!/bin/sh

php bin/console assets:install --symlink --relative

php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:clear --env=dev --no-warmup

chown -R www-data var

