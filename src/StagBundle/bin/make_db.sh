#!/bin/sh

# blob data
mkdir -p var/data-stagbundle-blob
for all in $(ls src/StagBundle/bin/*jpg); do
	FILENAME=$(basename $all)
	FILENAMESUM=$(echo $FILENAME | md5sum | awk '{print $1}')
	DATAPATH="$(pwd)/var/data-stagbundle-blob/${FILENAMESUM}"
	$MYSQL -NBe "insert into blobx (file_name,data_path) values ('${FILENAME}', '${DATAPATH}')" $DATABASE
	cp $all var/data-stagbundle-blob/${FILENAMESUM}
done

# course data
TEXT1="Vase prihlaska byla prijata."
TEXT2="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
TEXT3="Zveme vás na kurz bachaty 4 pro pokročilé

Kurz je určen pro ty, kteří navštěvovali náš kurz bachata 3 nebo ty, kteří již bachatu tančí a chtějí se v ní posunout dále. Bachata je romantický, pomalý tanec, který se často hraje na salsa tančírnách.Čekají na vás efektní figury, muzikalita, footwork a lehké padačky.

Bachatu milujeme a rádi bychom tuto vášeň s vámi sdíleli.

### Základní informace

* Lektoři: Jakub Peca, Jana Kučerová
* Kdy - pondělí od 11.9 - 13.11.2017 od 20.30-21.30(10 lekcí po 60 minutách)
* Kde - taneční sál v indické restauraci Masala Ghar, [nám. Republiky 21, Plzeň](https://www.google.cz/search?q=n%C3%A1m.+Republiky+21%2C+Plze%C5%88), 2.patro
* Cena: 1000,-/osobu

Hlásit se můžete i bez partnera, do určité míry přijímáme přhlášky od samotných dam. Snažíme se, aby poměr pánů a dam byl na kurzu vyrovnaný.

Přihlášky na mail: jana.kucerova@tanecvplzni.cz nebo do zpráv na FB. platba před první lekcí.

### O lektorech

**Jakub Peca** - tanci se věnuje od svých 15 let, závodně tančil standard a latinu. Později přešel k salse a bachatě, které začal před 5 lety vyučovat v Mostě pod TŠ Kamily Hlavačikové.
Loni skončil na 2.místě v bachata Jack and Jill, profi třídě lektoři. Mezi jeho oblíbené lektory bachaty, u kterých měl možnost se učit, patří například  [Daniel y Desireé](https://www.google.cz/search?q=Daniel+y+Desire%C3%A9&tbm=vid).

**Jana Kučerová** - je zakladatelkou Tance v Plzni, z. s., tančí od 9 let. Vyučuje salsu 5 let a bachatu druhým rokem. Neustále se snaží zdokonalovat u českých i zahraničních lektorů.
V letošním roce se umístili na 4. místě v Mistrovství ČR v bachatě, vystupují na akcích po celé ČR."

$MYSQL -NBe "insert into course (name,type,level,description,lecturer,place,color,appl_email_text,picture_ref_id) values ('SALSA 1', 'regular', 'zacatecnici', '${TEXT3}', 'Strejda', 'Masala Ghar', '#527dce', '${TEXT1}', 2)" $DATABASE
$MYSQL -NBe "insert into course (name,type,level,description,lecturer,place,color,appl_email_text,picture_ref_id) values ('BACHATA 2', 'regular', 'pokrocily', '${TEXT2}', 'Mamka', 'Salon Rounda', '#a874cc', '${TEXT1}', 1)" $DATABASE
$MYSQL -NBe "insert into course (name,type,level,description,lecturer,place,color,appl_email_text,picture_ref_id) values ('WORKOUT 3', 'regular', 'susinky vod klavesnic', '${TEXT2}', 'Spajdrmen', 'Lochotin', '#ffac5e', '${TEXT1}', 3)" $DATABASE


# lesson data
FORMAT='+%Y-%m-%d %H:%M'
NOW=$(date "${FORMAT}")

COURSE_ID=$($MYSQL -NBe "select id from course where name='SALSA 1'" $DATABASE)
for all in "$(date --date="monday -7 days 18:00" "${FORMAT}")" "$(date --date="monday 18:00" "${FORMAT}")" "$(date --date="monday +7 days 18:00" "${FORMAT}")" "$(date --date="monday +14 days 18:00" "${FORMAT}")"; do
	$MYSQL -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 90)" $DATABASE
done

COURSE_ID=$($MYSQL -NBe "select id from course where name='BACHATA 2'" $DATABASE)
for all in "$(date --date="monday -7 days 19:00" "${FORMAT}")" "$(date --date="monday 19:00" "${FORMAT}")" "$(date --date="monday +7 days 19:00" "${FORMAT}")" "$(date --date="monday +14 days 19:00" "${FORMAT}")"; do
	$MYSQL -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 60)" $DATABASE
done

COURSE_ID=$($MYSQL -NBe "select id from course where name='WORKOUT 3'" $DATABASE)
for all in "$(date --date="monday -7 days 19:30" "${FORMAT}")" "$(date --date="monday 19:30" "${FORMAT}")" "$(date --date="monday +7 days 19:30" "${FORMAT}")" "$(date --date="monday +14 days 19:30" "${FORMAT}")"; do
	$MYSQL -NBe "insert into lesson (course_id,time,length) values (${COURSE_ID}, '${all}', 45)" $DATABASE
done


# participant data
$MYSQL -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,deposit,payment,created,modified) values (${COURSE_ID}, 'tanecnik', 'josef', 'josef.tanecnik@tanecvplzni.cz','male','single', NULL, 'cash', 'cash', '${NOW}','${NOW}')" $DATABASE
$MYSQL -NBe "insert into participant (course_id,sn,gn,email,gender,paired,partner,deposit,payment,created,modified) values (${COURSE_ID}, 'tanecnice', 'eva', 'eva.tanecnice@tanecvplzni.cz', 'female', 'pair', 'alois netanecnik', 'wire-transfer', NULL, '${NOW}','${NOW}')" $DATABASE


TEXTL="Luděk se k tanci dostal po středoškolských tanečních, kdy se začal věnovat soutěžnímu 
tancování a vytancoval si mezinárodní třídu M ve standardních tancích. Již po pár letech 
se stal vedoucím tanečního klubu v Přerově, trenérem soutěžních párů a také lektorem 
středoškolských tanečních. Ke konci soutěžní kariéry poznal karibské tance salsa, bachata 
a merengue, kterým se po krátké době taktéž začal věnovat i lektorsky. Jeho velkou láskou se pak 
ale staly brazilské tance, zejména zouk, ve kterém si vybudoval uznávané jméno po celém světě a jezdí 
učit na mezinárodní festivaly. Se svojí ženou Pavlou založili před pár lety taneční studio Stolárna, 
ve kterém rozdávají lásku ke všem uvedeným tancům spoustě lidem :-)

### Které tance u nás Luděk učí
zouk, kizomba, společenské tance

### Jaké další tance má rád
všechny, které mají techniku a systém

### Co ještě má rád kromě tance
cestování, fotografování a dobré jídlo"

$MYSQL -NBe "insert into course (name,type,description,lecturer,place,color,appl_email_text,picture_ref_id) values ('Zouk s Luďkem Lužným', 'workshop', '${TEXTL}', 'Ludek Luzny', 'Masala Ghar', '#527dce', '${TEXT1}', 2)" $DATABASE
COURSE_ID=$($MYSQL -NBe "select id from course where name='Zouk s Luďkem Lužným'" $DATABASE)


$MYSQL -NBe "insert into lesson (course_id,time,length,level,lecturer,description) values (${COURSE_ID}, '$(date --date="saturday 10:30" "${FORMAT}")', 45, 'zacatecnici', 'Ludek Luzny a Tanecnice z Brna', 'zaklady zouku')" $DATABASE
$MYSQL -NBe "insert into lesson (course_id,time,length,level,lecturer,description) values (${COURSE_ID}, '$(date --date="saturday 12:30" "${FORMAT}")', 45, 'pokrocili', 'Ludek Luzny a Tanecnice z Brna', 'otocky, zvedacky, padacky')" $DATABASE
$MYSQL -NBe "insert into lesson (course_id,time,length,level,lecturer,description) values (${COURSE_ID}, '$(date --date="saturday 14:30" "${FORMAT}")', 45, 'vsechny urovne', 'Ludek Luzny', 'muzikalita')" $DATABASE
