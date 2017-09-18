<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends StagWebTestCase {
	protected $client = null;
	protected $em = null;
	protected $courseRepo = null;

	public function createTestCourse($em) {
		$tmp = new Course();
        	$tmp->setName("kurz test");
		$tmp->setType("regular");
		$tmp->setLevel("zacatecnici");
		$tmp->setDescription("kurz test popis");
		$tmp->setLecturer("ucitel");
		$tmp->setPlace("tanecni sal");
		$tmp->setColor("#eeffee");
		$tmp->setFbEventUrl("https://www.facebook.com/groups/".str_shuffle("1188703337868985")."/");
		$tmp->setFbGroupUrl("https://www.facebook.com/events/".str_shuffle("504753496544609")."/");
		$tmp->setActive(true);
		return $tmp;
	}
	
	
	
	
	
	public function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }

		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
	}
	public function tearDown() {
		parent::tearDown();
	}
	
	
	
	
    
	public function testListAction() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/course/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Kurzy")')->count());
	}



	public function testAddAction() {
		$this->logIn();
		
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." add ".mt_rand());


		$crawler = $this->client->request("GET", "/course/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[name]' => $testCourse->getName(),
			'course[type]' => $testCourse->getType(),
			'course[level]' => $testCourse->getLevel(),
            		'course[description]' => $testCourse->getDescription(),
            		'course[lecturer]' => $testCourse->getLecturer(),
            		'course[place]' => $testCourse->getPlace(),
			'course[color]' => $testCourse->getColor(),
			'course[active]' => $testCourse->getActive(),
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$course = $this->courseRepo->findOneByName($testCourse->getName());
        	$this->assertNotNull($course);
        	$this->assertSame($testCourse->getDescription(), $course->getDescription());

		$this->em->remove($course);
		$this->em->flush();
    	}



    	public function testEditAction() {
    		$this->logIn();
    		
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." edit ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/course/edit/{$testCourse->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[description]' => "edited description",
			'course[lecturer]' => "edited lecturer"
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	#$this->em->refresh($testCourse); //not sure why I have to refresh, doctrine magic

		# check general attributes change
		$course = $this->courseRepo->findOneById($testCourse->getId());
        	$this->assertNotNull($course);
        	$this->assertSame("edited description", $course->getDescription());
        	$this->assertSame("edited lecturer", $course->getLecturer());
		
		$this->em->remove($course);
		$this->em->flush();
    	}




	public function testDeleteAction() {
		$this->logIn();
		
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." delete ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/delete/{$testCourse->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$course = $this->courseRepo->findOneByName($testCourse->getName());
		$this->assertNull($course);
	}



	public function testScheduleAction() {
		$this->logIn();

		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." schedule ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		$this->em->clear();


		$crawler = $this->client->request("GET", "/course/schedule/{$testCourse->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course_schedule[length]' => 13,
			'course_schedule[schedule]' => "01.01.2001 01:01\n02.02.2002 02:02\n",
            	]);
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$course = $this->courseRepo->findOneByName($testCourse->getName());
		$this->assertNotNull($course);
		$this->assertSame(count($course->getLessons()), 2);
		$this->assertSame($course->getLessons()[0]->getLength(), 13);

		foreach ($course->getLessons() as $tmp) { $this->em->remove($tmp); }
		$this->em->remove($course);
		$this->em->flush();
	}
	
	
	
	public function testBookAction() {
		$this->logIn();

		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." book ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();


		$crawler = $this->client->request("GET", "/course/book/{$testCourse->getID()}");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());		
		$this->assertGreaterThan(0, $crawler->filter("li:contains('{$testCourse->getName()}')")->count());

		$this->em->remove($testCourse);
		$this->em->flush();
	}



	public function testSuggestPlaceAction() {
		$this->logIn();

		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." suggest_place ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();

		$crawler = $this->client->request("GET", "/course/suggest/place");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$places = json_decode($this->client->getResponse()->getContent());
		$this->assertNotNull($places);
		$this->assertGreaterThan(0, count($places));

		$this->em->remove($testCourse);
		$this->em->flush();
	}


	public function testSuggestLecturerAction() {
		$this->logIn();

		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." suggest_lecturer ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();

		$crawler = $this->client->request("GET", "/course/suggest/lecturer");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$lecturers = json_decode($this->client->getResponse()->getContent());
		$this->assertNotNull($lecturers);
		$this->assertGreaterThan(0, count($lecturers));

		$this->em->remove($testCourse);
		$this->em->flush();
	}



	public function testMenuListAction() {
		$this->logIn();

		$crawler = $this->client->request("GET", "/course/menulist");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
	}



	public function testActiveAction() {
		$this->logIn();

		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." active ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		$this->em->clear();

		$crawler = $this->client->request("GET", "/course/active/{$testCourse->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$course = $this->courseRepo->findOneByName($testCourse->getName());
		$this->assertSame(!$testCourse->getActive(), $course->getActive());

		$this->em->remove($course);
		$this->em->flush();
	}







	public function testShowAction() {
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." show ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/show/{$testCourse->getID()}");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

		$this->assertGreaterThan(0, $crawler->filter('html:contains("'.$testCourse->getName().'")')->count());
		
		$this->em->remove($testCourse);
		$this->em->flush();
	}
	
	public function testGridAction() {
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." grid ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/grid");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$this->assertGreaterThan(0, $crawler->filter('div:contains("'.$testCourse->getName().'")')->count());
		
		$this->em->remove($testCourse);
		$this->em->flush();
	}



	public function testUnauthenticatedInactiveCourseShowAction() {
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." unauth inactive show ".mt_rand());
		$testCourse->setActive(false);
		$this->em->persist($testCourse);
		$this->em->flush();


		$crawler = $this->client->request("GET", "/course/show/{$testCourse->getID()}");
		# existing but inacive course returns denied which leads to login
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->assertRegExp("/\/login$/", $this->client->getResponse()->headers->get("location"));

		$this->em->remove($testCourse);
		$this->em->flush();
	}



	public function testUnauthenticatedInactiveCourseGridAction() {
		$testCourse = $this->createTestCourse($this->em);
		$testCourse->setName($testCourse->getName()." unauth inactive grid ".mt_rand());
		$testCourse->setActive(false);
		$this->em->persist($testCourse);
		$this->em->flush();


		$crawler = $this->client->request("GET", "/course/grid");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$this->assertSame(0, $crawler->filter('div:contains("'.$testCourse->getName().'")')->count());

		$this->em->remove($testCourse);
		$this->em->flush();
	}
}

?>

