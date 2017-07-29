php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create

mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,color) values ('SALSA 1', 'init db', 'make db', 'db', 3, 0, 100, 200, '#527dce')" stagtvp
mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,color) values ('BACHATA 2', 'init db', 'make db', 'db', 3, 0, 300, 400, '#6b249c')" stagtvp
mysql -NBe "insert into course (name,description,teacher,place,capacity,pair,price_single,price_pair,color) values ('WORKOUT 3', 'init db', 'make db', 'db', 4, 0, 500, 600, '#ff7c00')" stagtvp

FORMAT='+%Y-%m-%d %H:%M'
NOW=$(date "${FORMAT}")

COURSE_ID=$(mysql -NBe "select id from course where name='SALSA 1'" stagtvp)
for all in "$(date --date="monday -7 days 18:00" "${FORMAT}")" "$(date --date="monday 18:00" "${FORMAT}")" "$(date --date="monday +7 days 18:00" "${FORMAT}")" "$(date --date="monday +14 days 18:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 90)" stagtvp
done
mysql -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,paid,created,modified) values (${COURSE_ID}, 'tanecnik', 'josef', 'josef.tanecnik@tanecvplzni.cz','male','single',NULL,0,'${NOW}','${NOW}')" stagtvp

COURSE_ID=$(mysql -NBe "select id from course where name='BACHATA 2'" stagtvp)
for all in "$(date --date="monday -7 days 19:00" "${FORMAT}")" "$(date --date="monday 19:00" "${FORMAT}")" "$(date --date="monday +7 days 19:00" "${FORMAT}")" "$(date --date="monday +14 days 19:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 60)" stagtvp
done

COURSE_ID=$(mysql -NBe "select id from course where name='WORKOUT 3'" stagtvp)
for all in "$(date --date="monday -7 days 19:30" "${FORMAT}")" "$(date --date="monday 19:30" "${FORMAT}")" "$(date --date="monday +7 days 19:30" "${FORMAT}")" "$(date --date="monday +14 days 19:30" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 45)" stagtvp
done

mysql -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,paid,created,modified) values (${COURSE_ID}, 'tanecnice', 'eva', 'eva.tanecnice@tanecvplzni.cz','female','pair','alois netanecnik',1,'${NOW}','${NOW}')" stagtvp
