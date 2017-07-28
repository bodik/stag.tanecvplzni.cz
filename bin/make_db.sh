php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair) values ('kurz init 1', 'init db', 'make db', 'db', 3, 0, 100, 200)" stagtvp
mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair) values ('kurz init 2', 'init db', 'make db', 'db', 3, 0, 300, 400)" stagtvp

FORMAT='+%Y-%m-%d %H:%M'

COURSE_ID=$(mysql -NBe "select id from course where name='kurz init 1'" stagtvp)
for all in "$(date --date="monday 18:00" "${FORMAT}")" "$(date --date="monday +7 days 18:00" "${FORMAT}")" "$(date --date="monday +14 days 18:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 90)" stagtvp
done

COURSE_ID=$(mysql -NBe "select id from course where name='kurz init 2'" stagtvp)
for all in "$(date --date="monday 19:00" "${FORMAT}")" "$(date --date="monday +7 days 19:00" "${FORMAT}")" "$(date --date="monday +14 days 19:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 60)" stagtvp
done

