<?php

namespace GuserBundle\Tests\Controller;

use GuserBundle\CryptPasswordEncoder;
use GuserBundle\Entity\User;
use GuserBundle\Tests\GuserWebTestCase;
use Symfony\Component\HttpFoundation\Response;


class UserControllerTest extends GuserWebTestCase {


	public function createTestUser() {
		$tmp = new User();
        	$tmp->setUsername("testuser");
		$tmp->setPassword(User::generatePassword());
		$tmp->setEmail("testuser@localhost");
		$tmp->setActive(true);
		return $tmp;
	}






	public function testUnauthenticatedActions() {
		$crawler = $this->client->request('GET', '/user/list');
		$this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
		$crawler = $this->client->request('GET', '/user/add');
		$this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
		$crawler = $this->client->request('GET', '/user/edit/1');
		$this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
		$crawler = $this->client->request('GET', '/user/delete/1');
		$this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
	}
	
	
	
	public function testListAction() {
		$this->logIn();
		
		$crawler = $this->client->request('GET', '/user/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("User list")')->count());
	}



	public function testAddAction() {
		$this->logIn();

		$testUser = $this->createTestUser();
		$testUser->setUsername($testUser->getUsername()." add ".mt_rand());
		$testUser->setEmail("add_".mt_rand()."_".$testUser->getEmail());
		$tmpPassword = User::generatePassword();

		
		$crawler = $this->client->request("GET", "/user/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'user[username]' => $testUser->getUsername(),
			'user[email]' => $testUser->getEmail(),
			'user[password]' => $tmpPassword,
            		'user[active]' => false,
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$user = $this->userRepo->findOneByUsername($testUser->getUsername());
        	$this->assertNotNull($user);
        	$this->assertSame($testUser->getUsername(), $user->getUsername());
		$this->assertSame(false, $user->getActive());
		
		$encoder = $this->client->getContainer()->get('security.encoder_factory')->getEncoder($user);
		//$encoder = $user->getPasswordEncoder(); // should return the same
        	$this->assertSame($encoder->encodePassword($tmpPassword, $user->getSalt()), $user->getPassword());
		
		$this->em->remove($user);
		$this->em->flush();
    	}
    	
    	
    	
    	public function testEditAction() {
		$this->logIn();

		# create a test user
		$testUser = $this->createTestUser();
		$testUser->setUsername($testUser->getUsername()." edit ".mt_rand());
		$testUser->setEmail("edit_".mt_rand()."_".$testUser->getEmail());
		$tmpPassword = User::generatePassword();
		$this->em->persist($testUser);
		$this->em->flush();
		$this->em->clear();
		
		
		# edit user
		$crawler = $this->client->request("GET", "/user/edit/{$testUser->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'user[username]' => $testUser->getUsername(),
			'user[email]' => "edited_".$testUser->getEmail(),
			'user[password]' => $tmpPassword,     		
            		'user[active]' => false,
        	]);
        	$this->client->submit($form);
		$changetime = new \Datetime();
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		# check general attributes change
		$user = $this->userRepo->findOneByUsername($testUser->getUsername());
        	$this->assertNotNull($user);
        	$this->assertSame("edited_".$testUser->getEmail(), $user->getEmail());
		$this->assertSame(false, $user->getActive());
		
		# check proper password change and encoding 
		$encoder = $this->client->getContainer()->get('security.encoder_factory')->getEncoder($user);
        	$this->assertSame($encoder->encodePassword($tmpPassword, $user->getSalt()), $user->getPassword());

		# check proper lastpasswordchange field update with epsilon < 2sec for request processing delays 
		$this->assertLessThan(2, abs($changetime->getTimestamp() - $user->getLastPasswordChange()->getTimestamp()));
		
		$this->em->remove($user);
		$this->em->flush();
    	}



	public function testDeleteAction() {
		$this->logIn();
		
		# create a test user
		$testUser = $this->createTestUser();
		$testUser->setUsername($testUser->getUsername()." delete ".mt_rand());
		$testUser->setEmail("delete_".mt_rand()."_".$testUser->getEmail());
		$this->em->persist($testUser);
		$this->em->flush();
	

		$crawler = $this->client->request("GET", "/user/delete/{$testUser->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$user = $this->userRepo->findOneByUsername($testUser->getUsername());
		$this->assertNull($user);
	}
	
	
	
	
	
		
	public function testLogin() {
		$this->logIn();
	}

	public function testFailedLogin() {
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->filter('button[type="submit"]')->form([
			'_username' => "notexist",
			'_password' => "notvalid",
		]);
		$this->client->submit($form);
		$this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->client->followRedirect();
		$this->assertContains("Invalid credentials.", $this->client->getResponse()->getContent());
	}






	public function testChangePasswordAction() {
		$this->logIn();


		# reauth
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => "notcorrect",
			"change_password[new_password1]" => "anything",
			"change_password[new_password2]" => "anything",
		]);
		$this->client->submit($form);
		$this->assertContains("Reauthentication failed.", $this->client->getResponse()->getContent());


		# not match
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => $this->testAdminPassword,
			"change_password[new_password1]" => "anything1",
			"change_password[new_password2]" => "anything2",
		]);


		# short
		$tmp = str_repeat("a", CryptPasswordEncoder::PASSWORD_MIN_LENGTH - 1);
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => $this->testAdminPassword,
			"change_password[new_password1]" => $tmp,
			"change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("Password minimal length", $this->client->getResponse()->getContent());


		# class
		$tmp = str_repeat("a", CryptPasswordEncoder::PASSWORD_MIN_LENGTH);
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => $this->testAdminPassword,
			"change_password[new_password1]" => $tmp,
			"change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("character classes", $this->client->getResponse()->getContent());


		# includes
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => $this->testAdminPassword,
			"change_password[new_password1]" => $this->testAdminUsername."ABC.123",
			"change_password[new_password2]" => $this->testAdminUsername."ABC.123",
		]);
		$this->client->submit($form);
		$this->assertContains("must not be based on username", $this->client->getResponse()->getContent());


		# change
		$tmp = User::generatePassword();
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"change_password[current_password]" => $this->testAdminPassword,
			"change_password[new_password1]" => $tmp,
			"change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("Password changed.", $this->client->getResponse()->getContent());
		

		# test change really works 
		$this->testAdminPassword = $tmp;
		$this->logIn();
		$crawler = $this->client->request("GET", "/user/changepassword");
		$this->assertGreaterThan(0, $crawler->filter('html:contains("User change password")')->count());
	}
	
	
	
	public function testLostPasswordAction() {

		# create a test user
		$testUser = $this->createTestUser();
		$testUser->setUsername($testUser->getUsername()." lostpassword ".mt_rand());
		$testUser->setEmail("lostpassword_".mt_rand()."_".$testUser->getEmail());
		$this->em->persist($testUser);
		$this->em->flush();
		

		# get the reset token
		$crawler = $this->client->request("GET", "/user/lostpassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"lost_password[email]" => $testUser->getEmail(),
		]);
		$this->client->submit($form);
		$this->assertContains("Lost password reset information was sent", $this->client->getResponse()->getContent());
		
		//$this->em->refresh($user); //must refresh on change without em
		$user = $this->userRepo->findOneByUsername($testUser->getUsername());
		$this->assertNotNull($user);
		
		# use the reset token
		$crawler = $this->client->request("GET", "/user/lostpassword/{$user->getLostPasswordToken()}");
		$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$this->assertGreaterThan(0, $crawler->filter('html:contains("User Set Password")')->count());
		
		$tmpPassword = User::generatePassword();
		$form = $crawler->filter('button[type="submit"]')->form([
			"set_password[new_password1]" => $tmpPassword,
			"set_password[new_password2]" => $tmpPassword,
		]);
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		
		# test new password
		$crawler = $this->client->request('GET', '/login');
		$form = $crawler->filter('button[type="submit"]')->form([
			'_username' => $user->getUsername(),
			'_password' => $tmpPassword,
		]);
		$this->client->submit($form);
		$this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
		$this->client->followRedirect();
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		
		$this->em->remove($user);
		$this->em->flush();
	}
	
}

?>
