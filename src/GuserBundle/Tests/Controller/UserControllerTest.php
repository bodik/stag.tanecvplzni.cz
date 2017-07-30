<?php

namespace Tests\GuserBundle\Controller;

use GuserBundle\CryptPasswordEncoder;
use GuserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\GuserBundle\CorreoWebTestCase;

class UserControllerTest extends CorreoWebTestCase {



	public function testUnauthenticatedActions() {
		$crawler = $this->client->request('GET', '/user');
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

		$username = 'testuser_'.mt_rand();
		$email = "{$username}@gc-system.cz";
		$password = User::generatePassword();


		$crawler = $this->client->request("GET", "/user/add");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'user[username]' => $username,
			'user[email]' => $email,
			'user[password]' => $password,
            		'user[active]' => false,
        	]);
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$user = $this->userRepo->findOneByUsername($username);
        	$this->assertNotNull($user);
        	$this->assertSame($username, $user->getUsername());
		$this->assertSame(false, $user->getActive());
		
        	$encoder = $this->client->getContainer()->get('security.encoder_factory')->getEncoder($user);
		//$encoder = $user->getPasswordEncoder(); // should return the same
		$encodedPassword = $encoder->encodePassword($password, $user->getSalt());
        	$this->assertSame($encodedPassword, $user->getPassword());
		
		$this->em->remove($user);
		$this->em->flush();
    	}
    	
    	
    	
    	public function testEditAction() {
		$this->logIn();

		# create a test user
		$username = 'testuser_'.mt_rand();
		$email = "{$username}@gc-system.cz";
        	$user = new User();
        	$user->setUsername($username);
		$user->setEmail($email);
		$user->setPassword(User::generatePassword()); //forgettable at first
		$user->setActive(true);
		$this->em->persist($user);
		$this->em->flush();
		
		$password = User::generatePassword(); //edited later
		
		# edit user
		$crawler = $this->client->request("GET", "/user/edit/{$user->getID()}");
		$form = $crawler->filter('button[type="submit"]')->form([
            		'user[username]' => $username,
			'user[email]' => $email,
			'user[password]' => $password,     		
            		'user[active]' => false,
        	]);
        	$this->client->submit($form);
		$changetime = new \Datetime();
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	$this->em->refresh($user); //must refresh on change without em

		# check general attributes change
		$user = $this->userRepo->findOneByUsername($username);		
        	$this->assertNotNull($user);
        	$this->assertSame($username, $user->getUsername());
		$this->assertSame(false, $user->getActive());
		
		# check proper password change and encoding 
		$encoder = $this->client->getContainer()->get('security.encoder_factory')->getEncoder($user);
		//$encoder = $user->getPasswordEncoder(); // should return the same
		$encodedPassword = $encoder->encodePassword($password, $user->getSalt());
        	$this->assertSame($encodedPassword, $user->getPassword());

		# check proper lastpasswordchange field update with epsilon < 2sec for request processing delays 
		$this->assertLessThan(2, abs($changetime->getTimestamp() - $user->getLastPasswordChange()->getTimestamp()));
		
		$this->em->remove($user);
		$this->em->flush();
    	}



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
			"user_change_password[current_password]" => "notcorrect",
			"user_change_password[new_password1]" => "anything",
			"user_change_password[new_password2]" => "anything",
		]);
		$this->client->submit($form);
		$this->assertContains("Reauthentication failed.", $this->client->getResponse()->getContent());


		# not match
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"user_change_password[current_password]" => $this->autotestAdminPassword,
			"user_change_password[new_password1]" => "anything1",
			"user_change_password[new_password2]" => "anything2",
		]);


		# short
		$tmp = str_repeat("a", CryptPasswordEncoder::PASSWORD_MIN_LENGTH - 1);
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"user_change_password[current_password]" => $this->autotestAdminPassword,
			"user_change_password[new_password1]" => $tmp,
			"user_change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("Password minimal length", $this->client->getResponse()->getContent());


		# class
		$tmp = str_repeat("a", CryptPasswordEncoder::PASSWORD_MIN_LENGTH);
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"user_change_password[current_password]" => $this->autotestAdminPassword,
			"user_change_password[new_password1]" => $tmp,
			"user_change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("character classes", $this->client->getResponse()->getContent());


		# includes
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"user_change_password[current_password]" => $this->autotestAdminPassword,
			"user_change_password[new_password1]" => $this->autotestAdminUsername."ABC.123",
			"user_change_password[new_password2]" => $this->autotestAdminUsername."ABC.123",
		]);
		$this->client->submit($form);
		$this->assertContains("must not be based on username", $this->client->getResponse()->getContent());


		# change
		$tmp = User::generatePassword();
		$crawler = $this->client->request("GET", "/user/changepassword");
		$form = $crawler->filter('button[type="submit"]')->form([
			"user_change_password[current_password]" => $this->autotestAdminPassword,
			"user_change_password[new_password1]" => $tmp,
			"user_change_password[new_password2]" => $tmp,
		]);
		$this->client->submit($form);
		$this->assertContains("Password changed.", $this->client->getResponse()->getContent());
		

		# test change really works 
		$this->autotestAdminPassword = $tmp;
		$this->logIn();
		$crawler = $this->client->request("GET", "/user/changepassword");
		$this->assertGreaterThan(0, $crawler->filter('html:contains("User change password")')->count());
	}
}

?>
