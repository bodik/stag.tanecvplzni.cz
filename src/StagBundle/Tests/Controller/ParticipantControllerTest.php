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
		"name" => "Josef Tanečník",
		"email" => "josef.tanecnik@tanecvplzni.cz",
		"partner" => "marie.tanecnice@tanecvplzni.cz",
		"phoneNumber" => "+420123456789",
		"gender" => Participant::ALL_GENDERS["MALE"],
		"paid" => false,
		"note" => "note1",	
	];
	protected $testCourse;
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get("doctrine")->getManager();
		$this->participantRepo = $this->em->getRepository("StagBundle:Participant");
		
		$testCourseData = (new CourseControllerTest())->testCourse;
		$this->testCourse = new Course();		
		$this->testCourse->setName($testCourseData["name"]." add ".mt_rand());
		$this->testCourse->setDescription($testCourseData["description"]);
		$this->testCourse->setTeacher($testCourseData["teacher"]);
		$this->testCourse->setPlace($testCourseData["place"]);
		$this->testCourse->setCapacity($testCourseData["capacity"]);
		$this->testCourse->setPair($testCourseData["pair"]);
		$this->testCourse->setPriceSingle($testCourseData["priceSingle"]);
		$this->testCourse->setPricePair($testCourseData["pricePair"]); 
		$this->testCourse->setLessons($testCourseData["lessons"]); 
		$this->em->persist($this->testCourse);
		$this->em->flush();		
	}
	
	protected function tearDown() {
		$courseRepo = $this->em->getRepository("StagBundle:Course");
		$this->em->remove($courseRepo->findOneByName($this->testCourse->getName()));
		$this->em->flush();
	}
    
	public function testList() {
        	$crawler = $this->client->request('GET', '/participant');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Účastníci")')->count());
	}



	public function testAddAction() {
		$this->testParticipant["name"] = $this->testParticipant["name"]." add ".mt_rand();
						
		$crawler = $this->client->request("GET", "/participant/add");
		$form = $crawler->filter('button[type="submit"]')->form([
			'participant[courseRef]' => $this->testCourse->getId(),
		        'participant[name]' => $this->testParticipant["name"],
            		'participant[email]' => $this->testParticipant["email"],
            		'participant[partner]' => $this->testParticipant["partner"],
            		'participant[phoneNumber]' => $this->testParticipant["phoneNumber"],
            		'participant[gender]' => $this->testParticipant["gender"],
            		'participant[paid]' => $this->testParticipant["paid"],
            		'participant[note]' => $this->testParticipant["note"],
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$participant = $this->participantRepo->findOneByName($this->testParticipant["name"]);
        	$this->assertNotNull($participant);
        	$this->assertSame($this->testParticipant["name"], $participant->getName());
		$this->assertSame($this->testParticipant["paid"], $participant->getPaid());

		$this->em->remove($participant);
		$this->em->flush();
    	}


    	public function testEditAction() {
		$this->testParticipant["name"] = $this->testParticipant["name"]." edit ".mt_rand();

        	$participant = new Participant();
        	$participant->setName($this->testParticipant["name"]);
		$participant->setEmail($this->testParticipant["email"]);
		$participant->setPhoneNumber($this->testParticipant["phoneNumber"]);
		$participant->setGender($this->testParticipant["gender"]);
		$participant->setPartner($this->testParticipant["partner"]);
		$participant->setPaid($this->testParticipant["paid"]);
		$participant->setNote($this->testParticipant["note"]);
		$participant->setCourseRef($this->testCourse);
		$this->em->persist($participant);
		$this->em->flush();
		
		$crawler = $this->client->request("GET", "/participant/edit/{$participant->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'participant[email]' => "edited email",
			'participant[gender]' => Participant::ALL_GENDERS["FEMALE"],
           		'participant[paid]' => true,
            	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		//TODO: not sure why this does not work here as expected based on correo project		
		//$this->em->refresh($course); //must refresh on change without em        	
        	
		# check general attributes change
		$participant = $this->participantRepo->findOneById($participant->getID());
        	$this->assertNotNull($participant);
        	$this->assertSame("edited email", $participant->getEmail());
        	$this->assertSame(Participant::ALL_GENDERS["FEMALE"], $participant->getGender());
		$this->assertSame(true, $participant->getPaid());
		
		$this->em->remove($participant);
		$this->em->flush();
    	}

	public function testDeleteAction() {
		$this->testParticipant["name"] = $this->testParticipant["name"]." delete ".mt_rand();
    		
        	$participant = new Participant();
        	$participant->setName($this->testParticipant["name"]);
		$participant->setEmail($this->testParticipant["email"]);
		$participant->setPhoneNumber($this->testParticipant["phoneNumber"]);
		$participant->setGender($this->testParticipant["gender"]);
		$participant->setPartner($this->testParticipant["partner"]);
		$participant->setPaid($this->testParticipant["paid"]);
		$participant->setNote($this->testParticipant["note"]);
		$participant->setCourseRef($this->testCourse);
		$this->em->persist($participant);
		$this->em->flush();		

		$crawler = $this->client->request("GET", "/participant/delete/{$participant->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$participant = $this->participantRepo->findOneByName($this->testParticipant["name"]);
		$this->assertNull($participant);
	}

}

?>

