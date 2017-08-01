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

	public function createTestCourse() {
		$tmp = new Course();
        	$tmp->setName("kurz test");
		$tmp->setDescription("kurz test popis");
		$tmp->setTeacher("ucitel");
		$tmp->setPlace("tanecni sal");
		$tmp->setCapacity(10);
		$tmp->setPair(true);
		$tmp->setPriceSingle(130);
		$tmp->setPricePair(200);
		$tmp->setColor("#eeffee");
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
	
	
	
	
    
	public function testList() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/course/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Courses")')->count());
	}



	public function testAddAction() {
		$this->logIn();
		
		$testCourse = $this->createTestCourse();
		$testCourse->setName($testCourse->getName()." add ".mt_rand());


		$crawler = $this->client->request("GET", "/course/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[name]' => $testCourse->getName(),
            		'course[description]' => $testCourse->getDescription(),
            		'course[teacher]' => $testCourse->getTeacher(),
            		'course[place]' => $testCourse->getPlace(),
            		'course[capacity]' => $testCourse->getCapacity(),
            		'course[pair]' => $testCourse->getPair(),
            		'course[priceSingle]' => $testCourse->getPriceSingle(),
            		'course[pricePair]' => $testCourse->getPricePair(),
			'course[color]' => $testCourse->getColor()
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$course = $this->courseRepo->findOneByName($testCourse->getName());
        	$this->assertNotNull($course);
        	$this->assertSame($testCourse->getDescription(), $course->getDescription());
		$this->assertSame($testCourse->getPair(), $course->getPair());

		$this->em->remove($course);
		$this->em->flush();
    	}



    	public function testEditAction() {
    		$this->logIn();
    		
		$testCourse = $this->createTestCourse();
		$testCourse->setName($testCourse->getName()." edit ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/course/edit/{$testCourse->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'course[description]' => "edited description",
			'course[teacher]' => "edited teacher",
           		'course[pair]' => false,
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	#$this->em->refresh($testCourse); //not sure why I have to refresh, doctrine magic

		# check general attributes change
		$course = $this->courseRepo->findOneById($testCourse->getId());
        	$this->assertNotNull($course);
        	$this->assertSame("edited description", $course->getDescription());
        	$this->assertSame("edited teacher", $course->getTeacher());
		$this->assertSame(false, $course->getPair());
		
		$this->em->remove($course);
		$this->em->flush();
    	}




	public function testDeleteAction() {
		$this->logIn();
		
		$testCourse = $this->createTestCourse();
		$testCourse->setName($testCourse->getName()." delete ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/delete/{$testCourse->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$course = $this->courseRepo->findOneByName($testCourse->getName());
		$this->assertNull($course);
	}






	public function testPriceAction() {
		$testCourse = $this->createTestCourse();
		$testCourse->setName($testCourse->getName()." price ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		
				
		$crawler = $this->client->request("GET", "/course/price/{$testCourse->getId()}/single");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		
		$this->em->remove($testCourse);
		$this->em->flush();
	}



	public function testShowAction() {
		$testCourse = $this->createTestCourse();
		$testCourse->setName($testCourse->getName()." show ".mt_rand());
		$this->em->persist($testCourse);
		$this->em->flush();
		    		
    		
		$crawler = $this->client->request("GET", "/course/show/{$testCourse->getID()}");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

		$this->assertGreaterThan(0, $crawler->filter('html:contains("'.$testCourse->getName().'")')->count());
		
		$this->em->remove($testCourse);
		$this->em->flush();
	}
}

?>

