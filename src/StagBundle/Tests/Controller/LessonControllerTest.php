<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Lesson;
use StagBundle\Tests\Controller\CourseControllerTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LessonControllerTest extends WebTestCase {
	private $client;
	private $em;
	private $lessonRepo;

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
		$tmp->setCourseRef($this->testCourse);
		return $tmp;
	}
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get("doctrine")->getManager();
		$this->lessonRepo = $this->em->getRepository("StagBundle:Lesson");
		
		$tmp = new CourseControllerTest();
		$tmp->setUp();	
		$this->testCourse = $tmp->createTestCourse($tmp->testCourse);
		$this->em->persist($this->testCourse);
		$this->em->flush();
	}
	
	protected function tearDown() {
		$courseRepo = $this->em->getRepository("StagBundle:Course");
		$this->em->remove($courseRepo->findOneById($this->testCourse->getId()));
		$this->em->flush();
	}
    
	public function testList() {
        	$crawler = $this->client->request('GET', '/lesson');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Lessons")')->count());
	}



	public function testAddAction() {
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
		$this->testLesson["note"] = $this->testLesson["note"]." delete ".mt_rand();
		$lesson = $this->createTestLesson($this->testLesson);
		$this->em->persist($lesson);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/lesson/delete/{$lesson->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		//$lesson = $this->lessonRepo->findOneByNote($this->testLesson["note"]);
		//$this->assertNull($lesson);
	}
	
}

?>

