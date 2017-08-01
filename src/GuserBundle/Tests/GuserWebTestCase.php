<?php

namespace GuserBundle\Tests;

use GuserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GuserWebTestCase extends WebTestCase {
	
	protected $client;
	protected $em;
	protected $userRepo;
	
	protected $testAdminUsername = "autotestadmin";
	protected $testAdminPassword = null;
	public function createTestAdmin() {
        	$this->testAdminPassword = User::generatePassword();

		$tmp = new User();
        	$tmp->setUsername($this->testAdminUsername);
		$tmp->setPassword($this->testAdminPassword);
		$tmp->setEmail("autotestadmin@localhost");
		$tmp->setActive(true);
		$tmp->setRoles(["ROLE_ADMIN"]);
		return $tmp;
	}







	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
		$this->userRepo = $this->em->getRepository('GuserBundle:User');

		// create user for performing the tests
		$tmp = $this->userRepo->findOneByUsername($this->testAdminUsername);
		if(empty($tmp)) {				
			$tmp = $this->createTestAdmin();
			$this->em->persist($tmp);
			$this->em->flush();
		}	
	}
	
	
	
	protected function tearDown() {
		// cleanup user for performing the tests
		$tmp = $this->userRepo->findOneByUsername($this->testAdminUsername);
		if($tmp) {
			$this->em->remove($tmp);
			$this->em->flush();
		}
	}
	
	
	
	protected function logIn() {
		// login as user performing the tests
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->filter('button[type="submit"]')->form([
			'_username' => $this->testAdminUsername,
			'_password' => $this->testAdminPassword,
		]);
		$this->client->submit($form);
		$this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->client->followRedirect();
		if (Response::HTTP_OK === $this->client->getResponse()->getStatusCode()) {
			// Redirected URL is OK
			//dump($this->client->getResponse());
			return true;
		} else {
			return false;
		}
	}
}

?>
