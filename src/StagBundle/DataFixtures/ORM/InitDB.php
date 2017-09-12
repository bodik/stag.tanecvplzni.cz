<?php

namespace StagBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use StagBundle\Entity\Blob;
use StagBundle\Entity\Course;
use StagBundle\Entity\Lesson;
use StagBundle\Entity\Ticket;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class InitDB implements FixtureInterface, ContainerAwareInterface {
	
	protected $container;
	public function setContainer(ContainerInterface $container = null) {
		$this->container = $container;
	}




	public $text1 = "Vase prihlaska byla prijata.";	
	public $text2 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
	public $text3 = "Zveme vás na kurz bachaty 4 pro pokročilé

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
V letošním roce se umístili na 4. místě v Mistrovství ČR v bachatě, vystupují na akcích po celé ČR.";

	public $textl="Luděk se k tanci dostal po středoškolských tanečních, kdy se začal věnovat soutěžnímu 
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
cestování, fotografování a dobré jídlo";


	public $textsn="Zveme vás na pravidelnou úterní tančírnu - salsy, bachaty, kizomby a zouku do Kalikováku. Od 20.30-21h se bude hrát kizomba, zouk. Od 21h pomalá salsa a bachata a tempo se bude postupně zrychlovat a nebo taky ne :)

## Vstupné: 20,-Kč";


	public function load(ObjectManager $manager) {

		$datadir = $this->container->get('kernel')->getRootDir()."/../var/data-stagbundle-blob";
		mkdir($datadir, 0755, true);
		$datadir = realpath($datadir);
		foreach (new \DirectoryIterator(dirname(__FILE__)."/../../bin") as $fileInfo) {
			if($fileInfo->isFile() and $fileInfo->getExtension() == "jpg") {
				$blob = new Blob();
				$tmpName = md5(uniqid(mt_rand(), true));
				$blob->setFileName($fileInfo->getFilename());
				$blob->setDataPath("{$datadir}/{$tmpName}");
				copy($fileInfo->getPathname(), "{$datadir}/{$tmpName}");
				$manager->persist($blob);
				$manager->flush();
			}
		}






		$course = new Course();
		$course->setName("SALSA 1");
		$course->setType("regular");
		$course->setLevel("zacatecnici");
		$course->setDescription($this->text3);
		$course->setLecturer("Strejda");
		$course->setPlace("Masala Ghar, Nám. republiky 21");
		$course->setColor("#527dce");
		$course->setApplEmailText($this->text1);
		$course->setPictureRef($manager->getRepository("StagBundle:Blob")->findOneByFileName("salsa1.jpg"));
		$manager->persist($course);
		$manager->flush();

		$d = (new \Datetime())->setTimestamp(strtotime("monday last week 18:00"));
		for ($i = 0; $i < 4; $i++) {
			$d->add( new \DateInterval('P'.($i*7).'D'));
			$lesson = new Lesson();
			$lesson->setTime($d);
			$lesson->setLength(90);
			$lesson->setCourseRef($course);
			$manager->persist($lesson);
			$manager->flush();
		}
		
		$ticket = new Ticket();
		$ticket->setName("Jednotlivec");
		$ticket->setPrice(100);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();
		$ticket = new Ticket();
		$ticket->setName("Taneční pár");
		$ticket->setPrice(90);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();






		$course = new Course();
		$course->setName("BACHATA 2");
		$course->setType("regular");
		$course->setLevel("pokrocily");
		$course->setDescription($this->text2);
		$course->setLecturer("Mamka");
		$course->setPlace("Salon Rounda");
		$course->setColor("#a874cc");
		$course->setApplEmailText($this->text1);
		$course->setPictureRef($manager->getRepository("StagBundle:Blob")->findOneByFileName("bachata1.jpg"));
		$manager->persist($course);
		$manager->flush();
		
		$d = (new \Datetime())->setTimestamp(strtotime("monday last week 19:00"));
		for ($i = 0; $i < 4; $i++) {
			$d->add( new \DateInterval('P'.($i*7).'D'));
			$lesson = new Lesson();
			$lesson->setTime($d);
			$lesson->setLength(60);
			$lesson->setCourseRef($course);
			$manager->persist($lesson);
			$manager->flush();
		}

		$ticket = new Ticket();
		$ticket->setName("Jednotlivec");
		$ticket->setPrice(100);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();
		$ticket = new Ticket();
		$ticket->setName("Taneční pár");
		$ticket->setPrice(90);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();






		$course = new Course();
		$course->setName("WORKOUT 3");
		$course->setType("regular");
		$course->setLevel("susinky vod klavesnic");
		$course->setDescription($this->text2);
		$course->setLecturer("Spajdrmen");
		$course->setPlace("Lochotin");
		$course->setColor("#ffac5e");
		$course->setApplEmailText($this->text1);
		$course->setPictureRef($manager->getRepository("StagBundle:Blob")->findOneByFileName("workout1.jpg"));
		$manager->persist($course);
		$manager->flush();

		$d = (new \Datetime())->setTimestamp(strtotime("monday last week 19:30"));
		for ($i = 0; $i < 4; $i++) {
			$d->add( new \DateInterval('P'.($i*7).'D'));
			$lesson = new Lesson();
			$lesson->setTime($d);
			$lesson->setLength(45);
			$lesson->setCourseRef($course);
			$manager->persist($lesson);
			$manager->flush();
		}

		$ticket = new Ticket();
		$ticket->setName("Jednotlivec");
		$ticket->setPrice(100);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();
		$ticket = new Ticket();
		$ticket->setName("Taneční pár");
		$ticket->setPrice(90);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();







		$course = new Course();
		$course->setName("Zouk s Luďkem Lužným");
		$course->setType("workshop");
		$course->setDescription($this->textl);
		$course->setLecturer("Luděk Lužný");
		$course->setPlace("Masala Ghar, Nám. republiky 21");
		$course->setColor("#aabbcc");
		$course->setApplEmailText($this->text1);
		$course->setPictureRef($manager->getRepository("StagBundle:Blob")->findOneByFileName("testblob.jpg"));
		$manager->persist($course);
		$manager->flush();
		
		$d = (new \Datetime())->setTimestamp(strtotime("saturday this week 11:00"));
		$lesson = new Lesson();
		$lesson->setTime($d);
		$lesson->setLength(45);
		$lesson->setLevel("zacatecnici");
		$lesson->setLecturer("Ludek Luzny a Tanecnice z Brna");
		$lesson->setDescription("zaklady zouku");
		$lesson->setCourseRef($course);
		$manager->persist($lesson);
		$manager->flush();
		
		$d = (new \Datetime())->setTimestamp(strtotime("saturday this week 13:00"));
		$lesson = new Lesson();
		$lesson->setTime($d);
		$lesson->setLength(45);
		$lesson->setLevel("pokrocili");
		$lesson->setLecturer("Ludek Luzny a Tanecnice z Brna");
		$lesson->setDescription("otocky, zvedacky, padacky");
		$lesson->setCourseRef($course);
		$manager->persist($lesson);
		$manager->flush();
		
		$d = (new \Datetime())->setTimestamp(strtotime("saturday this week 14:00"));
		$lesson = new Lesson();
		$lesson->setTime($d);
		$lesson->setLength(45);
		$lesson->setLevel("vsechny urovne");
		$lesson->setLecturer("Ludek Luzny");
		$lesson->setDescription("muzikalita");
		$lesson->setCourseRef($course);
		$manager->persist($lesson);
		$manager->flush();
		
		$ticket = new Ticket();
		$ticket->setName("FullPass");
		$ticket->setPrice(500);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();
		
		$ticket = new Ticket();
		$ticket->setName("1 lekce");
		$ticket->setPrice(300);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();
		
		$ticket = new Ticket();
		$ticket->setName("2 lekce");
		$ticket->setPrice(400);
		$ticket->setCourseRef($course);
		$manager->persist($ticket);
		$manager->flush();

		
		
		
		$course = new Course();
		$course->setName("SALSA NIGHT v Kalikováku");
		$course->setType("party");
		$course->setLevel("Salsa, Bachata, Zouk, Kizomba");
		$course->setDescription($this->textsn);
		$course->setLecturer("DJ Mareska");
		$course->setPlace("Kalikovský mlýn");
		$course->setColor("#ff0000");
		$course->setPictureRef($manager->getRepository("StagBundle:Blob")->findOneByFileName("workout1.jpg"));
		$manager->persist($course);
		$manager->flush();

		$d = (new \Datetime())->setTimestamp(strtotime("tuesday last week 19:30"));
		for ($i = 0; $i < 10; $i++) {
			$d->add( new \DateInterval('P'.($i*7).'D'));
			$lesson = new Lesson();
			$lesson->setTime($d);
			$lesson->setLength(240);
			$lesson->setCourseRef($course);
			$manager->persist($lesson);
			$manager->flush();
		}
	}
}

?>