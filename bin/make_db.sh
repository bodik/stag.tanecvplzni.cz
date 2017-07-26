php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

mysql -NBe 'insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,lessons) values ("kurz init 1", "init db", "make db", "db", 3, 0, 100, 200, "a:2:{i:0;s:16:\"2017-01-01T12:00\";i:1;s:16:\"2017-01-03T12:00\";}")' stagtvp
mysql -NBe 'insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,lessons) values ("kurz init 2", "init db", "make db", "db", 3, 0, 300, 400, "a:2:{i:0;s:16:\"2017-01-01T12:00\";i:1;s:16:\"2017-01-03T12:00\";}")' stagtvp
