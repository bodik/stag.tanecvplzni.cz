<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Participant;
use StagBundle\Tests\Controller\CourseControllerTest;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ParticipantControllerTest extends StagWebTestCase {
	protected $client;
	protected $em;
	protected $participantRepo;

	protected $testCourse;
	public function createTestParticipant() {
        	$tmp = new Participant();
        	$tmp->setSn("Tanečník");
        	$tmp->setGn("Josef");
		$tmp->setEmail("josef.tanecnik@localhost");
		$tmp->setPhoneNumber("+420123456789");
		$tmp->setGender(Participant::ALL_GENDERS["muž"]);
		$tmp->setPaired(Participant::ALL_PAIRS["v páru"]);
		$tmp->setPartner("marie.tanecnice@localhost");
		$tmp->setReference("facebook");
		$tmp->setNote("poznamka k prihlascce");
		$tmp->setPaid(false);
		$tmp->setCourseRef($this->courseRepo->findOneById($this->testCourse->getId()));
		return $tmp;
	}






	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->participantRepo = $this->em->getRepository("StagBundle:Participant");
		$this->courseRepo = $this->em->getRepository("StagBundle:Course");
		
		$this->testCourse = (new CourseControllerTest())->createTestCourse();
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

        	$crawler = $this->client->request('GET', '/participant/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Participants")')->count());
	}



	public function testAddAction() {
		$this->logIn();
		
		$testParticipant = $this->createTestParticipant();
		$testParticipant->setSn($testParticipant->getSn()." add ".mt_rand());
		
						
		$crawler = $this->client->request("GET", "/participant/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'participant[courseRef]' => $this->testCourse->getId(),
		        'participant[sn]' => $testParticipant->getSn(),
		        'participant[gn]' => $testParticipant->getGn(),
            		'participant[email]' => $testParticipant->getEmail(),
			'participant[paired]' => $testParticipant->getPaired(),
            		'participant[partner]' => $testParticipant->getPartner(),
            		'participant[phoneNumber]' => $testParticipant->getPhoneNumber(),
            		'participant[gender]' => $testParticipant->getGender(),
            		'participant[reference]' => $testParticipant->getReference(),
			'participant[note]' => $testParticipant->getNote(),
            		'participant[paid]' => $testParticipant->getPaid(),
            		
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$participant = $this->participantRepo->findOneBySn($testParticipant->getSn());
        	$this->assertNotNull($participant);
        	$this->assertSame($testParticipant->getSn(), $participant->getSn());
		$this->assertSame($testParticipant->getPaid(), $participant->getPaid());

		$this->em->remove($participant);
		$this->em->flush();
    	}


    	public function testEditAction() {
		$this->logIn();
    		
		$testParticipant = $this->createTestParticipant();
		$testParticipant->setSn($testParticipant->getSn()." edit ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
		$this->em->clear();
		
		
		$crawler = $this->client->request("GET", "/participant/edit/{$testParticipant->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'participant[email]' => "edited email",
			'participant[gender]' => Participant::ALL_GENDERS["žena"],
           		'participant[paid]' => true,
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
		# check general attributes change
		$participant = $this->participantRepo->findOneById($testParticipant->getId());
        	$this->assertNotNull($participant);
        	$this->assertSame("edited email", $participant->getEmail());
		$this->assertSame(Participant::ALL_GENDERS["žena"], $participant->getGender());
		$this->assertSame(true, $participant->getPaid());
		
		$this->em->remove($participant);
		$this->em->flush();
    	}

	public function testDeleteAction() {
		$this->logIn();
		
		$testParticipant = $this->createTestParticipant();
		$testParticipant->setSn($testParticipant->getSn()." delete ".mt_rand());
		$this->em->persist($testParticipant);
		$this->em->flush();
    		

		$crawler = $this->client->request("GET", "/participant/delete/{$testParticipant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneBySn($testParticipant->getSn());
		$this->assertNull($participant);
	}
	
	
	
	public function testApplicationAction() {
		$testParticipant = $this->createTestParticipant();
		$testParticipant->setSn($testParticipant->getSn()." application ".mt_rand());
						
		$crawler = $this->client->request("GET", "/participant/application/".$this->testCourse->getId());
		$form = $crawler->filter('button[type="submit"]')->form([
		        'participant_application[sn]' => $testParticipant->getSn(),
		        'participant_application[gn]' => $testParticipant->getGn(),
            		'participant_application[email]' => $testParticipant->getEmail(),
			'participant_application[paired]' => $testParticipant->getPaired(),
            		'participant_application[partner]' => $testParticipant->getPartner(),
            		'participant_application[phoneNumber]' => $testParticipant->getPhoneNumber(),
            		'participant_application[gender]' => $testParticipant->getGender(),
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
        	$this->assertSame($testParticipant->getPaired(), $participant->getPaired());
        	$this->assertSame($this->testCourse->getId(), $participant->getCourseRef()->getId());

		$this->em->remove($participant);
		$this->em->flush();
    	}
	
	

}

?>

