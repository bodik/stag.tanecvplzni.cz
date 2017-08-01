<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Lesson;
use StagBundle\Tests\Controller\CourseControllerTest;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LessonControllerTest extends StagWebTestCase {
	protected $client;
	protected $em;
	protected $lessonRepo;

	public $testLesson = [
		"time" => "2012-01-01 22:22:22",
		"length" => 60,
		"note" => "poznamecka",	
	];
	protected $testCourse;
	
	protected function createTestLesson($data) {
        	$tmp = new Lesson();
        	$tmp->setTime(new \DateTime($data["time"]));
        	$tmp->setLength($data["length"]);
		$tmp->setNote($data["note"]);
		$tmp->setCourseRef($this->courseRepo->findOneById($this->testCourse->getId()));
		return $tmp;
	}






	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->lessonRepo = $this->em->getRepository("StagBundle:Lesson");
		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
		
		$tmp = new CourseControllerTest();
		$this->testCourse = $tmp->createTestCourse($tmp->testCourse);
		$this->em->persist($this->testCourse);
		$this->em->flush();
	}
	protected function tearDown() {
		parent::tearDown();
		
		$courseRepo = $this->em->getRepository("StagBundle:Course");
		$this->em->remove($courseRepo->findOneById($this->testCourse->getId()));
		$this->em->flush();
	}






	public function testList() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/lesson/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Lessons")')->count());
	}



	public function testAddAction() {
		$this->logIn();		
		
		$this->testLesson["note"] = $this->testLesson["note"]." add ".mt_rand();
						
		$crawler = $this->client->request("GET", "/lesson/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'lesson[courseRef]' => $this->testCourse->getId(),
		        'lesson[time]' => $this->testLesson["time"],
		        'lesson[length]' => $this->testLesson["length"],
            		'lesson[note]' => $this->testLesson["note"],            		
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$lesson = $this->lessonRepo->findOneByNote($this->testLesson["note"]);
        	$this->assertNotNull($lesson);
        	$this->assertSame((new \DateTime($this->testLesson["time"]))->getTimestamp(), $lesson->getTime()->getTimestamp());
		$this->assertSame($this->testLesson["note"], $lesson->getNote());

		$this->em->remove($lesson);
		$this->em->flush();
    	}



    	public function testEditAction() {
		$this->logIn();    		
    		
		$this->testLesson["note"] = $this->testLesson["note"]." edit ".mt_rand();
		$lesson = $this->createTestLesson($this->testLesson);
		$this->em->persist($lesson);
		$this->em->flush();
		
		$crawler = $this->client->request("GET", "/lesson/edit/{$lesson->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'lesson[length]' => 45,
			'lesson[note]' => $this->testLesson["note"]." canceled",
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->em->refresh($lesson); //must refresh on change without em        	
        	
		# check general attributes change
		$lesson = $this->lessonRepo->findOneById($lesson->getID());
        	$this->assertNotNull($lesson);
        	$this->assertSame(45, $lesson->getLength());
		$this->assertSame($this->testLesson["note"]." canceled", $lesson->getNote());
		
		$this->em->remove($lesson);
		$this->em->flush();
    	}



	public function testDeleteAction() {
		$this->logIn();
		
		$this->testLesson["note"] = $this->testLesson["note"]." delete ".mt_rand();
		$lesson = $this->createTestLesson($this->testLesson);
		$this->em->persist($lesson);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/lesson/delete/{$lesson->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$lesson = $this->lessonRepo->findOneByNote($this->testLesson["note"]);
		$this->assertNull($lesson);
	}






	public function testCalendarAction() {
		$crawler = $this->client->request("GET", "/lesson/calendar");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
	}



	public function testEventsAction() {
		$this->testLesson["note"] = $this->testLesson["note"]." delete ".mt_rand();
		$lesson = $this->createTestLesson($this->testLesson);
		$this->em->persist($lesson);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/lesson/events");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

		$events = json_decode($this->client->getResponse()->getContent());
		$this->assertNotNull($events);
		
		$this->em->remove($lesson);
		$this->em->flush();
	}
	
}

?>

