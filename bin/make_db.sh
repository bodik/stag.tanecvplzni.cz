php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create


TODAY=$(date +%Y-%m-%d)
NEXT=$(date --date='next monday' +%Y-%m-%d)

mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,lesson_minutes,lessons) values ('kurz init 1', 'init db', 'make db', 'db', 3, 0, 100, 200, 75, 'a:2:{i:0;s:16:\"${TODAY}T12:00\";i:1;s:16:\"${NEXT}T12:00\";}')" stagtvp

mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,lesson_minutes,lessons) values ('kurz init 2', 'init db', 'make db', 'db', 3, 0, 300, 400, 60, 'a:2:{i:0;s:16:\"${TODAY}T12:00\";i:1;s:16:\"${NEXT}T12:00\";}')" stagtvp
