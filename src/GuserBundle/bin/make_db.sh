#!/bin/sh

#guser data
USERNAME="bodik"
EMAIL="bodik@cesnet.cz"
PASSWORD=$(/bin/dd if=/dev/urandom bs=100 count=1 2>/dev/null | /usr/bin/sha256sum | /usr/bin/awk '{print $1}')
ENCODED_PASSWORD=$(php bin/console security:encode-password --empty-salt --no-interaction --no-ansi $PASSWORD 2>/dev/null | grep 'Encoded password' | awk '{print $3}')
mysql -NBe "insert into user (username, password, roles, active, locked, email, failed_login_count, created, modified) values ('${USERNAME}', '${ENCODED_PASSWORD}', 'ROLE_ADMIN,ROLE_OPERATOR', 1, 0, '${EMAIL}', 0, now(), now())" $DATABASE
echo "INFO: password generated for $USERNAME $PASSWORD"

mysql -NBe "insert into user (username, password, roles, active, locked, email, failed_login_count, created, modified) values ('janakucerova', '*', 'ROLE_ADMIN,ROLE_OPERATOR', 1, 0, 'jana.kucerova@tanecvplzni.cz', 0, now(), now())" $DATABASE
mysql -NBe "insert into user (username, password, roles, active, locked, email, failed_login_count, created, modified) values ('pavlosherin', '*', 'ROLE_ADMIN,ROLE_OPERATOR', 1, 0, 'pavlo.sherin@tanecvplzni.cz', 0, now(), now())" $DATABASE
mysql -NBe "insert into user (username, password, roles, active, locked, email, failed_login_count, created, modified) values ('martinmareska', '*', 'ROLE_ADMIN,ROLE_OPERATOR', 1, 0, 'martin.mareska@tanecvplzni.cz', 0, now(), now())" $DATABASE

