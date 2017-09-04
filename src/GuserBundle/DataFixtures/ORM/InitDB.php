<?php

namespace GuserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GuserBundle\Entity\User;
use StagBundle\Entity\Lesson;
use StagBundle\Entity\Course;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class InitDB implements FixtureInterface, ContainerAwareInterface {
	
	protected $container;
	public function setContainer(ContainerInterface $container = null) {
		$this->container = $container;
	}






	public function load(ObjectManager $manager) {
		$password = User::generatePassword();
		$user = new User();
		$user->setUsername("bodik");
		$user->setPassword($password);
		$user->setRoles(["ROLE_ADMIN", "ROLE_OPERATOR"]);
		$user->setActive(true);
		$user->setLocked(false);
		$user->setEmail("bodik@cesnet.cz");
		$user->setFailedLoginCount(0);
		$manager->persist($user);
		$manager->flush();
		dump("{$user->getUsername()}:{$password}");
		
		
		
		$user = new User();
		$user->setUsername("janakucerova");
		$user->setRoles(["ROLE_ADMIN", "ROLE_OPERATOR"]);
		$user->setActive(true);
		$user->setLocked(false);
		$user->setEmail("jana.kucerova@tanecvplzni.cz");
		$user->setFailedLoginCount(0);
		$manager->persist($user);
		$manager->flush();		
		
		
		$user = new User();
		$user->setUsername("pavlosherin");
		$user->setRoles(["ROLE_ADMIN", "ROLE_OPERATOR"]);
		$user->setActive(true);
		$user->setLocked(false);
		$user->setEmail("pavlo.sherin@tanecvplzni.cz");
		$user->setFailedLoginCount(0);
		$manager->persist($user);
		$manager->flush();
		
		
		
		$user = new User();
		$user->setUsername("martinmareska");
		$user->setRoles(["ROLE_ADMIN", "ROLE_OPERATOR"]);
		$user->setActive(true);
		$user->setLocked(false);
		$user->setEmail("martin.mareska@tanecvplzni.cz");
		$user->setFailedLoginCount(0);
		$manager->persist($user);
		$manager->flush();			
		
	}
}

?>