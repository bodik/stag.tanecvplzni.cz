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

	protected $testCourse;
	public function createTestLesson() {
        	$tmp = new Lesson();
        	$tmp->setTime(new \DateTime("2012-01-01 22:22:22"));
        	$tmp->setLength(60);
		$tmp->setNote("poznamecka");
		$tmp->setCourseRef($this->courseRepo->findOneById($this->testCourse->getId()));
		return $tmp;
	}






	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->lessonRepo = $this->em->getRepository("StagBundle:Lesson");
		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
		
		$this->testCourse = (new CourseControllerTest())->createTestCourse();
		$this->em->persist($this->testCourse);
		$this->em->flush();
	}
	protected function tearDown() {
		parent::tearDown();
		
		$this->em->remove($this->courseRepo->findOneById($this->testCourse->getId()));
		$this->em->flush();
	}






	public function testList() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/lesson/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Lessons")')->count());
	}



	public function testAddAction() {
		$this->logIn();		
		
		$testLesson = $this->createTestLesson();
		$testLesson->setNote($testLesson->getNote()." add ".mt_rand());
						
		$crawler = $this->client->request("GET", "/lesson/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'lesson[courseRef]' => $this->testCourse->getId(),
		        'lesson[time]' => $testLesson->getTime()->format("c"),
		        'lesson[length]' => $testLesson->getLength(),
            		'lesson[note]' => $testLesson->getNote(),            		
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$lesson = $this->lessonRepo->findOneByNote($testLesson->getNote());
        	$this->assertNotNull($lesson);
        	$this->assertSame($testLesson->getTime()->getTimestamp(), $lesson->getTime()->getTimestamp());
		$this->assertSame($testLesson->getNote(), $lesson->getNote());

		$this->em->remove($lesson);
		$this->em->flush();
    	}



    	public function testEditAction() {
		$this->logIn();    		
    		
		$testLesson = $this->createTestLesson();
		$testLesson->setNote($testLesson->getNote()." edit ".mt_rand());
		$this->em->persist($testLesson);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/lesson/edit/{$testLesson->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'lesson[length]' => 45,
			'lesson[note]' => $testLesson->getNote()." canceled",
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
		# check general attributes change
		$lesson = $this->lessonRepo->findOneById($testLesson->getID());
        	$this->assertNotNull($lesson);
        	$this->assertSame(45, $lesson->getLength());
		$this->assertSame($testLesson->getNote()." canceled", $lesson->getNote());
		
		$this->em->remove($lesson);
		$this->em->flush();
    	}



	public function testDeleteAction() {
		$this->logIn();
		
		$testLesson = $this->createTestLesson();
		$testLesson->setNote($testLesson->getNote()." delete ".mt_rand());
		$this->em->persist($testLesson);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/lesson/delete/{$testLesson->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$lesson = $this->lessonRepo->findOneByNote($testLesson->getNote());
		$this->assertNull($lesson);
	}





	public function testCalendarAction() {
		$crawler = $this->client->request("GET", "/lesson/calendar");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
	}



	public function testEventsAction() {
		$testLesson = $this->createTestLesson();
		$testLesson->setNote($testLesson->getNote()." events ".mt_rand());
		$this->em->persist($testLesson);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/lesson/events");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

		$events = json_decode($this->client->getResponse()->getContent());
		$this->assertNotNull($events);
		
		$this->em->remove($testLesson);
		$this->em->flush();
	}
	

}

?>

