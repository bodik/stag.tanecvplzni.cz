<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Participant;
use StagBundle\Entity\Ticket;
use StagBundle\Tests\Controller\CourseControllerTest;
use StagBundle\Tests\Controller\TicketControllerTest;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ParticipantControllerTest extends StagWebTestCase {
	protected $client;
	protected $em;
	protected $participantRepo;

	public $testTicket;
	public $testCourse;
	public function createTestParticipant($em) {
        	$tmp = new Participant();
        	$tmp->setSn("Tanečník");
        	$tmp->setGn("Josef");
		$tmp->setEmail("josef.tanecnik@tanecvplzni.cz");
		$tmp->setPhoneNumber("+420123456789");
		$tmp->setGender(Participant::ALL_GENDERS["muž"]);
		$tmp->setPartner("marie.tanecnice@localhost");
		$tmp->setReference("facebook");
		$tmp->setNote("poznamka k prihlascce");
		$tmp->setDeposit(null);
		$tmp->setPayment(null);
		
		$ticketRepo = $em->getRepository("StagBundle:Ticket");
		$tmp->setTicketRef($ticketRepo->findOneById($this->testTicket->getId()));

		return $tmp;
	}






	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->participantRepo = $this->em->getRepository("StagBundle:Participant");
		
		$this->testCourse = (new CourseControllerTest())->createTestCourse($this->em);
		$this->em->persist($this->testCourse);
		$this->em->flush();
		
		$tl = new LessonControllerTest();
		$tl->testCourse = $this->testCourse;
		$this->testLesson = $tl->createTestLesson($this->em);
		$this->em->persist($this->testLesson);
		$this->em->flush();		
		
		$tk = new TicketControllerTest();
		$tk->testCourse = $this->testCourse;
		$this->testTicket = $tk->createTestTicket($this->em);
		$this->em->persist($this->testTicket);
		$this->em->flush();

	}
	
	protected function tearDown() {
		parent::tearDown();
		
		$ticketRepo = $this->em->getRepository("StagBundle:Ticket");
		$this->em->remove($ticketRepo->findOneById($this->testTicket->getId()));
		$this->em->flush();
		
		$lessonRepo = $this->em->getRepository("StagBundle:Lesson");
		$this->em->remove($lessonRepo->findOneById($this->testLesson->getId()));
		$this->em->flush();
		
		$courseRepo = $this->em->getRepository("StagBundle:Course");
		$this->em->remove($courseRepo->findOneById($this->testCourse->getId()));
		$this->em->flush();
	}





	public function testList() {
		$this->logIn();

        	$crawler = $this->client->request('GET', '/participant/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Participants")')->count());
	}



	public function testAddAction() {
		$this->logIn();
		
		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." add ".mt_rand());
		
						
		$crawler = $this->client->request("GET", "/participant/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'participant[ticketRef]' => $this->testTicket->getId(),
		        'participant[gn]' => $testParticipant->getGn(),
		        'participant[sn]' => $testParticipant->getSn(),
            		'participant[email]' => $testParticipant->getEmail(),
            		'participant[phoneNumber]' => $testParticipant->getPhoneNumber(),
            		'participant[gender]' => $testParticipant->getGender(),
            		'participant[partner]' => $testParticipant->getPartner(),
            		'participant[reference]' => $testParticipant->getReference(),
			'participant[note]' => $testParticipant->getNote(),
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$participant = $this->participantRepo->findOneBySn($testParticipant->getSn());
        	$this->assertNotNull($participant);
        	$this->assertSame($testParticipant->getSn(), $participant->getSn());
		$this->assertSame($testParticipant->getPhoneNumber(), $participant->getPhoneNumber());

		$this->em->remove($participant);
		$this->em->flush();
    	}



    	public function testEditAction() {
		$this->logIn();
    		
		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." edit ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/participant/edit/{$testParticipant->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'participant[email]' => "edited_email@tanecvplzni.cz",
			'participant[gender]' => Participant::ALL_GENDERS["žena"],
			'participant[payment]' => "wire",
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
		# check general attributes change
		$participant = $this->participantRepo->findOneById($testParticipant->getId());
        	$this->assertNotNull($participant);
        	$this->assertSame("edited_email@tanecvplzni.cz", $participant->getEmail());
		$this->assertSame(Participant::ALL_GENDERS["žena"], $participant->getGender());
		$this->assertSame("wire", $participant->getPayment());
		
		$this->em->remove($participant);
		$this->em->flush();
    	}



	public function testDeleteAction() {
		$this->logIn();
		
		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." delete ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
		$this->em->clear();
    		

		$crawler = $this->client->request("GET", "/participant/delete/{$testParticipant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneBySn($testParticipant->getSn());
		$this->assertNull($participant);
	}



	public function testDepositAction() {
		$this->logIn();

		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." deposit ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
		$this->em->clear(); // will change entity outside em


		$crawler = $this->client->request("GET", "/participant/deposit/{$testParticipant->getID()}/some%20deposit");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneById($testParticipant->getId());
		$this->assertNotNull($participant);
		$this->assertSame("some deposit", $participant->getDeposit());


		$this->em->clear(); // will change entity outside em
		$crawler = $this->client->request("GET", "/participant/deposit/{$testParticipant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneById($testParticipant->getId());
		$this->assertNotNull($participant);
		$this->assertSame(null, $participant->getDeposit());


		$this->em->remove($participant);
		$this->em->flush();
	}



	public function testPaymentAction() {
		$this->logIn();

		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." payment ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
		$this->em->clear(); // will change entity outside em


		$crawler = $this->client->request("GET", "/participant/payment/{$testParticipant->getID()}/some%20other%20payment");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneById($testParticipant->getId());
		$this->assertNotNull($participant);
		$this->assertSame("some other payment", $participant->getPayment());


		$this->em->clear(); // will change entity outside em
		$crawler = $this->client->request("GET", "/participant/payment/{$testParticipant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());


		$participant = $this->participantRepo->findOneById($testParticipant->getId());
		$this->assertNotNull($participant);
		$this->assertSame(null, $participant->getPayment());


		$this->em->remove($participant);
		$this->em->flush();
	}




	public function testApplicationAction() {
		$testParticipant = $this->createTestParticipant($this->em);
		$testParticipant->setSn($testParticipant->getSn()." application ".mt_rand());
						
		$crawler = $this->client->request("GET", "/participant/application/".$this->testTicket->getId());
		$form = $crawler->filter('button[type="submit"]')->form([
		        'participant_application[gn]' => $testParticipant->getGn(),
		        'participant_application[sn]' => $testParticipant->getSn(),
            		'participant_application[email]' => $testParticipant->getEmail(),
            		'participant_application[phoneNumber]' => $testParticipant->getPhoneNumber(),
            		'participant_application[gender]' => $testParticipant->getGender(),
            		'participant_application[partner]' => $testParticipant->getPartner(),
            		'participant_application[reference]' => $testParticipant->getReference(),
			'participant_application[note]' => $testParticipant->getNote(),
			'participant_application[tosagreed]' => 1,
        	]);
        	$crawler = $this->client->submit($form);
        	$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        	$this->assertGreaterThan(0, $crawler->filter('h1:contains("Vaše přihláška byla přijata")')->count());
        	
        	$participant = $this->participantRepo->findOneBySn($testParticipant->getSn());
        	$this->assertNotNull($participant);
        	$this->assertSame($testParticipant->getSn(), $participant->getSn());
        	$this->assertSame($this->testTicket->getId(), $participant->getTicketRef()->getId());

		$this->em->remove($participant);
		$this->em->flush();
    	}
	


}

?>

