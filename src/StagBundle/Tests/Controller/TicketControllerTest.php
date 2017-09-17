<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Ticket;
use StagBundle\Tests\Controller\CourseControllerTest;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TicketControllerTest extends StagWebTestCase {
	protected $client;
	protected $em;
	protected $ticketRepo;

	public $testCourse;
	public function createTestTicket($em) {
        	$tmp = new Ticket();
        	$tmp->setName("Jednotlivec");
        	$tmp->setPrice(60);
        	
		$courseRepo = $em->getRepository("StagBundle:Course");
		$tmp->setCourseRef($courseRepo->findOneById($this->testCourse->getId()));

		return $tmp;
	}






	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->ticketRepo = $this->em->getRepository("StagBundle:Ticket");
		
		$this->testCourse = (new CourseControllerTest())->createTestCourse($this->em);
		$this->em->persist($this->testCourse);
		$this->em->flush();
	}
	protected function tearDown() {
		parent::tearDown();
		
		$courseRepo = $this->em->getRepository("StagBundle:Course");		
		$this->em->remove($courseRepo->findOneById($this->testCourse->getId()));
		$this->em->flush();
	}






	public function testListAction() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/ticket/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Vstupy")')->count());
	}



	public function testAddAction() {
		$this->logIn();		
		
		$testTicket = $this->createTestTicket($this->em);
		$testTicket->setName($testTicket->getName()." add ".mt_rand());
						
		$crawler = $this->client->request("GET", "/ticket/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'ticket[courseRef]' => $this->testCourse->getId(),
		        'ticket[name]' => $testTicket->getName(),
		        'ticket[price]' => $testTicket->getPrice(),
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
		$ticket = $this->ticketRepo->findOneByName($testTicket->getName());
        	$this->assertNotNull($ticket);
        	$this->assertSame($testTicket->getName(), $ticket->getName());
		$this->assertSame($testTicket->getPrice(), $ticket->getPrice());

		$this->em->remove($ticket);
		$this->em->flush();
    	}



    	public function testEditAction() {
		$this->logIn();    		
    		
		$testTicket = $this->createTestTicket($this->em);
		$testTicket->setName($testTicket->getName()." edit ".mt_rand());
		$this->em->persist($testTicket);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/ticket/edit/{$testTicket->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'ticket[price]' => 45,
			'ticket[name]' => $testTicket->getName()." canceled",
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
		# check general attributes change
		$ticket = $this->ticketRepo->findOneById($testTicket->getID());
        	$this->assertNotNull($ticket);
        	$this->assertSame(45, $ticket->getPrice());
		$this->assertSame($testTicket->getName()." canceled", $ticket->getName());
		
		$this->em->remove($ticket);
		$this->em->flush();
    	}



	public function testDeleteAction() {
		$this->logIn();
		
		$testTicket = $this->createTestTicket($this->em);
		$testTicket->setName($testTicket->getName()." delete ".mt_rand());
		$this->em->persist($testTicket);
		$this->em->flush();
    		
		$crawler = $this->client->request("GET", "/ticket/delete/{$testTicket->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$ticket = $this->ticketRepo->findOneByName($testTicket->getName());
		$this->assertNull($ticket);
	}



}

?>

