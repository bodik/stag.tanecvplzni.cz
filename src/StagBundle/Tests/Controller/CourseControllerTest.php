<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends WebTestCase {
	private $client;
	private $em;
	private $courseRepo;

	public $testCourse = [
		"name" => "kurz test",
		"description" => "kurz test popis",
		"teacher" => "ucitel",
		"place" => "masala",
		"capacity" => 10,
		"pair" => true,
		"priceSingle" => 130,
		"pricePair" => 200
	];
	
	public function createTestCourse($data) {
		$tmp = new Course();
        	$tmp->setName($data["name"]);
		$tmp->setDescription($data["description"]);
		$tmp->setTeacher($data["teacher"]);
		$tmp->setPlace($data["place"]);
		$tmp->setCapacity($data["capacity"]);
		$tmp->setPair($data["pair"]);
		$tmp->setPriceSingle($data["priceSingle"]);
		$tmp->setPricePair($data["pricePair"]);
		return $tmp;
	}
	
	
	
	
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get("doctrine")->getManager();
		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
	}
	
	
	
	
    
	public function testList() {
        	$crawler = $this->client->request('GET', '/course');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Courses")')->count());
	}



	public function testAddAction() {
		$this->testCourse["name"] = $this->testCourse["name"]." add ".mt_rand();
						
		$crawler = $this->client->request("GET", "/course/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[name]' => $this->testCourse["name"],
            		'course[description]' => $this->testCourse["description"],
            		'course[teacher]' => $this->testCourse["teacher"],
            		'course[place]' => $this->testCourse["place"],
            		'course[capacity]' => $this->testCourse["capacity"],
            		'course[pair]' => $this->testCourse["pair"],
            		'course[priceSingle]' => $this->testCourse["priceSingle"],
            		'course[pricePair]' => $this->testCourse["pricePair"]
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$course = $this->courseRepo->findOneByName($this->testCourse["name"]);
        	$this->assertNotNull($course);
        	$this->assertSame($this->testCourse["name"], $course->getName());
		$this->assertSame($this->testCourse["pair"], $course->getPair());

		$this->em->remove($course);
		$this->em->flush();
    	}



    	public function testEditAction() {
		$this->testCourse["name"] = $this->testCourse["name"]." edit ".mt_rand();
		$course = $this->createTestCourse($this->testCourse);
		$this->em->persist($course);
		$this->em->flush();
    		
		
		$crawler = $this->client->request("GET", "/course/edit/{$course->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[description]' => "edited description",
			'course[teacher]' => "edited teacher",
           		'course[pair]' => false,
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		//TODO: not sure why this does not work here as expected based on correo project		
		//$this->em->refresh($course); //must refresh on change without em        	
        	
		# check general attributes change
		$course = $this->courseRepo->findOneById($course->getID());
        	$this->assertNotNull($course);
        	$this->assertSame("edited description", $course->getDescription());
        	$this->assertSame("edited teacher", $course->getTeacher());
		$this->assertSame(false, $course->getPair());
		
		$this->em->remove($course);
		$this->em->flush();
    	}




	public function testDeleteAction() {
		$this->testCourse["name"] = $this->testCourse["name"]." delete ".mt_rand();   
		$course = $this->createTestCourse($this->testCourse);
		$this->em->persist($course);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/delete/{$course->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$course = $this->courseRepo->findOneByName($this->testCourse["name"]);
		$this->assertNull($course);
	}
}

?>

