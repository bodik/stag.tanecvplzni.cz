<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Course;
use StagBundle\Entity\Participant;
use StagBundle\Tests\Controller\CourseControllerTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ParticipantControllerTest extends WebTestCase {
	private $client;
	private $em;
	private $participantRepo;

	public $testParticipant = [
		"sn" => "Tanečník",
		"gn" => "Josef",
		"email" => "josef.tanecnik@tanecvplzni.cz",
		"phoneNumber" => "+420123456789",
		"gender" => Participant::ALL_GENDERS["muž"],
		"paired" => Participant::ALL_PAIRS["v páru"],
		"partner" => "marie.tanecnice@tanecvplzni.cz",
		"reference" => "facebook",
		"note" => "poznamka k prihlascce",
		"paid" => false,
		"note" => "note1",	
	];
	protected $testCourse;
	
	protected function createTestParticipant($data) {
        	$tmp = new Participant();
        	$tmp->setSn($data["sn"]);
        	$tmp->setGn($data["gn"]);
		$tmp->setEmail($data["email"]);
		$tmp->setPhoneNumber($data["phoneNumber"]);
		$tmp->setGender($data["gender"]);
		$tmp->setPaired($data["paired"]);
		$tmp->setPartner($data["partner"]);
		$tmp->setReference($data["reference"]);
		$tmp->setNote($data["note"]);
		$tmp->setPaid($data["paid"]);
		$tmp->setCourseRef($this->testCourse);
		return $tmp;
	}
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get("doctrine")->getManager();
		$this->participantRepo = $this->em->getRepository("StagBundle:Participant");
		
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
        	$crawler = $this->client->request('GET', '/participant');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Participants")')->count());
	}



	public function testAddAction() {
		$this->testParticipant["sn"] = $this->testParticipant["sn"]." add ".mt_rand();
						
		$crawler = $this->client->request("GET", "/participant/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'participant[courseRef]' => $this->testCourse->getId(),
		        'participant[sn]' => $this->testParticipant["sn"],
		        'participant[gn]' => $this->testParticipant["gn"],
            		'participant[email]' => $this->testParticipant["email"],
			'participant[paired]' => $this->testParticipant["paired"],
            		'participant[partner]' => $this->testParticipant["partner"],
            		'participant[phoneNumber]' => $this->testParticipant["phoneNumber"],
            		'participant[gender]' => $this->testParticipant["gender"],
            		'participant[reference]' => $this->testParticipant["reference"],
			'participant[note]' => $this->testParticipant["note"],
            		'participant[paid]' => $this->testParticipant["paid"],
            		
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$participant = $this->participantRepo->findOneBySn($this->testParticipant["sn"]);
        	$this->assertNotNull($participant);
        	$this->assertSame($this->testParticipant["sn"], $participant->getSn());
		$this->assertSame($this->testParticipant["paid"], $participant->getPaid());

		$this->em->remove($participant);
		$this->em->flush();
    	}


    	public function testEditAction() {
		$this->testParticipant["sn"] = $this->testParticipant["sn"]." edit ".mt_rand();
		$participant = $this->createTestParticipant($this->testParticipant);
		$this->em->persist($participant);
		$this->em->flush();
		
		$crawler = $this->client->request("GET", "/participant/edit/{$participant->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'participant[email]' => "edited email",
			'participant[gender]' => Participant::ALL_GENDERS["žena"],
           		'participant[paid]' => true,
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->em->refresh($participant); //must refresh on change without em        	
        	
		# check general attributes change
		$participant = $this->participantRepo->findOneById($participant->getID());
        	$this->assertNotNull($participant);
        	$this->assertSame("edited email", $participant->getEmail());
		$this->assertSame(Participant::ALL_GENDERS["žena"], $participant->getGender());
		$this->assertSame(true, $participant->getPaid());
		
		$this->em->remove($participant);
		$this->em->flush();
    	}

	public function testDeleteAction() {
		$this->testParticipant["sn"] = $this->testParticipant["sn"]." delete ".mt_rand();
		$participant = $this->createTestParticipant($this->testParticipant);
		$this->em->persist($participant);
		$this->em->flush();
    		

		$crawler = $this->client->request("GET", "/participant/delete/{$participant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneBySn($this->testParticipant["sn"]);
		$this->assertNull($participant);
	}
	
	
	
	public function testApplicationAction() {
		$this->testParticipant["sn"] = $this->testParticipant["sn"]." application ".mt_rand();
						
		$crawler = $this->client->request("GET", "/participant/application/".$this->testCourse->getId());
		$form = $crawler->filter('button[type="submit"]')->form([
		        'participant_application[sn]' => $this->testParticipant["sn"],
		        'participant_application[gn]' => $this->testParticipant["gn"],
            		'participant_application[email]' => $this->testParticipant["email"],
			'participant_application[paired]' => $this->testParticipant["paired"],
            		'participant_application[partner]' => $this->testParticipant["partner"],
            		'participant_application[phoneNumber]' => $this->testParticipant["phoneNumber"],
            		'participant_application[gender]' => $this->testParticipant["gender"],
            		'participant_application[reference]' => $this->testParticipant["reference"],
			'participant_application[note]' => $this->testParticipant["note"],
			'participant_application[tosagreed]' => 1,
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$participant = $this->participantRepo->findOneBySn($this->testParticipant["sn"]);
        	$this->assertNotNull($participant);
        	$this->assertSame($this->testParticipant["sn"], $participant->getSn());
        	$this->assertSame($this->testParticipant["paired"], $participant->getPaired());
        	$this->assertSame($this->testCourse, $participant->getCourseRef());

		$this->em->remove($participant);
		$this->em->flush();
    	}
	
	

}

?>

