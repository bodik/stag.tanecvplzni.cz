<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CourseControllerTest extends WebTestCase {
	private $client;
	private $em;
	private $courseRepo;

	private $testCourse = [
		"name" => "kurz test",
		"description" => "kurz test popis",
		"teacher" => "ucitel",
		"place" => "masala",
		"capacity" => 10,
		"pair" => False,
		"priceSingle" => 130,
		"pricePair" => 200,
		"lessons" => ["l1","l2"]	
	];
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = $this->client->getContainer()->get("doctrine")->getManager();
		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
	}
	
	
    
	public function testList() {
        	$crawler = $this->client->request('GET', '/course');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Kurzy")')->count());
	}



	public function testAddAction() {
		$crawler = $this->client->request("GET", "/course/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[name]' => $this->testCourse["name"],
            		'course[description]' => $this->testCourse["description"],
            		'course[teacher]' => $this->testCourse["teacher"],
            		'course[place]' => $this->testCourse["place"],
            		'course[capacity]' => $this->testCourse["capacity"],
            		'course[pair]' => $this->testCourse["pair"],
            		'course[priceSingle]' => $this->testCourse["priceSingle"],
            		'course[pricePair]' => $this->testCourse["pricePair"],
            		#'course[lessons]' => $this->testCourse["lessons"],
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
        	$course = new Course();
        	$course->setName($this->testCourse["name"]);
		$course->setDescription($this->testCourse["description"]);
		$course->setTeacher($this->testCourse["teacher"]);
		$course->setPlace($this->testCourse["place"]);
		$course->setCapacity($this->testCourse["capacity"]);
		$course->setPair($this->testCourse["pair"]);
		$course->setPriceSingle($this->testCourse["priceSingle"]);
		$course->setPricePair($this->testCourse["pricePair"]); 
		$course->setLessons($this->testCourse["lessons"]); 
		$this->em->persist($course);
		$this->em->flush();
		
		$crawler = $this->client->request("GET", "/course/edit/{$course->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[description]' => "new description",
            		'course[lessons]' => $this->testCourse["lessons"] + ["l3"],
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	$this->em->refresh($course); //must refresh on change without em

		# check general attributes change
		//$course = $this->courseRepo->findOneByName($this->testCourse["name"]);		
        	//$this->assertNotNull($course);
        	//$this->assertSame("new description", $course->getDescription());
		
		$this->em->remove($course);
		$this->em->flush();
    	}



/*
	public function testDeleteAction() {
		$this->logIn();

		$username = 'testuser_'.mt_rand();
		$email = "{$username}@gc-system.cz";
		$user = new User();
		$user->setUsername($username);
		$user->setEmail($email);
		$user->setPassword(User::generatePassword()); //forgettable at first
		$user->setActive(false);
		$this->em->persist($user);
		$this->em->flush();

		$crawler = $this->client->request("GET", "/user/delete/{$user->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$user = $this->userRepo->findOneByUsername($username);
		$this->assertNull($user);
	}*/
}

?>

