<?php

namespace GuserBundle\Tests;

use GuserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GuserWebTestCase extends WebTestCase {
	
	protected $client;
	protected $em;
	protected $userRepo;
	
	protected $autotestAdminUsername = "autotestadmin";
	protected $autotestAdminEmail = "autotestadmin@localhost";
	protected $autotestAdminPassword;
	protected $autotestAdminRoles = ["ROLE_ADMIN"];
	
	
	
	
	protected function setUp() {
		$this->client = static::createClient();
		$this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
		$this->userRepo = $this->em->getRepository('GuserBundle:User');

		// create user for performing the tests
		$tmp = $this->userRepo->findOneByUsername($this->autotestAdminUsername);
		if(empty($tmp)) {				
			$tmp = new User();
			$tmp->setUsername($this->autotestAdminUsername);
			$tmp->setEmail($this->autotestAdminEmail);
			$tmp->setActive(true);
			$tmp->setRoles($this->autotestAdminRoles);		
		}	
		$this->autotestAdminPassword = User::generatePassword();
		$tmp->setPassword($this->autotestAdminPassword);
		$this->em->persist($tmp);
		$this->em->flush();
	}
	
	protected function tearDown() {
		// cleanup user for performing the tests
		$tmp = $this->userRepo->findOneByUsername($this->autotestAdminUsername);
		if(!empty($tmp)) {
			$this->em->remove($tmp);
			$this->em->flush();
		}
	}
	
	
	
	protected function logIn() {
		// login as user performing the tests
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->filter('button[type="submit"]')->form([
			'_username' => $this->autotestAdminUsername,
			'_password' => $this->autotestAdminPassword,
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
