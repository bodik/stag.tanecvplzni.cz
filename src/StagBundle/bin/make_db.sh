#!/bin/sh

# blob data
mkdir -p var/data-stagbundle-blob
for all in $(ls src/StagBundle/bin/*jpg); do
	FILENAME=$(basename $all)
	FILENAMESUM=$(echo $FILENAME | md5sum | awk '{print $1}')
	DATAPATH="$(pwd)/var/data-stagbundle-blob/${FILENAMESUM}"
	mysql -NBe "insert into blobx (file_name,data_path) values ('${FILENAME}', '${DATAPATH}')" $DATABASE
	cp $all var/data-stagbundle-blob/${FILENAMESUM}
done

# course data
TEXT1="Vase prihlaska byla prijata."
TEXT2="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."

mysql -NBe "insert into course (name,level,description,teacher,place,capacity,pair,price_single,price_pair,color,appl_email_text,picture_ref_id) values ('SALSA 1', 'zacatecnici', '${TEXT2}', 'Ucitel', 'Masala Ghar', 3, 1, 100, 200, '#527dce', '${TEXT1}', 2)" $DATABASE
mysql -NBe "insert into course (name,level,description,teacher,place,capacity,pair,price_single,price_pair,color,appl_email_text,picture_ref_id) values ('BACHATA 2', 'pokrocily', '${TEXT2}', 'Ucitelka', 'Salon Rounda', 3, 1, 300, 400, '#a874cc', '${TEXT1}', 1)" $DATABASE
mysql -NBe "insert into course (name,level,description,teacher,place,capacity,pair,price_single,price_pair,color,appl_email_text,picture_ref_id) values ('WORKOUT 3', 'susinky vod klavesnic', '${TEXT2}', 'Pivo dela hezka tela', 'Lochotin', 4, 0, 500, 600, '#ffac5e', '${TEXT1}', 3)" $DATABASE


# lesson data
FORMAT='+%Y-%m-%d %H:%M'
NOW=$(date "${FORMAT}")

COURSE_ID=$(mysql -NBe "select id from course where name='SALSA 1'" $DATABASE)
for all in "$(date --date="monday -7 days 18:00" "${FORMAT}")" "$(date --date="monday 18:00" "${FORMAT}")" "$(date --date="monday +7 days 18:00" "${FORMAT}")" "$(date --date="monday +14 days 18:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 90)" $DATABASE
done
mysql -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,paid,paytime,created,modified) values (${COURSE_ID}, 'tanecnik', 'josef', 'josef.tanecnik@tanecvplzni.cz','male','single', NULL, 0, NULL, '${NOW}','${NOW}')" $DATABASE

COURSE_ID=$(mysql -NBe "select id from course where name='BACHATA 2'" $DATABASE)
for all in "$(date --date="monday -7 days 19:00" "${FORMAT}")" "$(date --date="monday 19:00" "${FORMAT}")" "$(date --date="monday +7 days 19:00" "${FORMAT}")" "$(date --date="monday +14 days 19:00" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 60)" $DATABASE
done

COURSE_ID=$(mysql -NBe "select id from course where name='WORKOUT 3'" $DATABASE)
for all in "$(date --date="monday -7 days 19:30" "${FORMAT}")" "$(date --date="monday 19:30" "${FORMAT}")" "$(date --date="monday +7 days 19:30" "${FORMAT}")" "$(date --date="monday +14 days 19:30" "${FORMAT}")"; do
	mysql -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 45)" $DATABASE
done


# participant data
mysql -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,paid,paytime,created,modified) values (${COURSE_ID}, 'tanecnice', 'eva', 'eva.tanecnice@tanecvplzni.cz','female','pair','alois netanecnik',1, '${NOW}', '${NOW}','${NOW}')" $DATABASE

