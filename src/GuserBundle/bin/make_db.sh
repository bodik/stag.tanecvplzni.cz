#!/bin/sh

#guser data
USERNAME="bodik"
EMAIL="bodik@cesnet.cz"
PASSWORD=$(/bin/dd if=/dev/urandom bs=100 count=1 2>/dev/null | /usr/bin/sha256sum | /usr/bin/awk '{print $1}')
ENCODED_PASSWORD=$(php bin/console security:encode-password --empty-salt --no-interaction --no-ansi $PASSWORD 2>/dev/null | grep 'Encoded password' | awk '{print $3}')
mysql -NBe "insert into user (username,email,password,created,modified,active,roles) values ('$USERNAME', '$EMAIL', '$ENCODED_PASSWORD', now(), now(), 1, 'ROLE_ADMIN,ROLE_OPERATOR')" $DATABASE
echo "INFO: password generated for $USERNAME $PASSWORD"

mysql -NBe "insert into user (username,email,password,created,modified,active,roles) values ('janakucerova', 'jana.kucerova@tanecvplzni.cz', '*', now(), now(), 1, 'ROLE_ADMIN,ROLE_OPERATOR')" $DATABASE
mysql -NBe "insert into user (username,email,password,created,modified,active,roles) values ('pavlosherin', 'pavlo.sherin@tanecvplzni.cz', '*', now(), now(), 1, 'ROLE_ADMIN,ROLE_OPERATOR')" $DATABASE
mysql -NBe "insert into user (username,email,password,created,modified,active,roles) values ('martinmareska', 'martin.mareska@tanecvplzni.cz', '*', now(), now(), 1, 'ROLE_ADMIN,ROLE_OPERATOR')" $DATABASE


