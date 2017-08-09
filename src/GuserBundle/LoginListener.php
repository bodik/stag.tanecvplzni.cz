<?php

namespace GuserBundle;

use Doctrine\ORM\EntityManagerInterface;
use GuserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener {
	private $log;
	private $container;
	private $em;
	
	public function __construct(LoggerInterface $log, ContainerInterface $container, EntityManagerInterface $em) {
		$this->log = $log;
		$this->em = $em;
		$this->container = $container;
	}

	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
		$username = $event->getAuthenticationToken()->getUser()->getUsername();
		$remote_addr = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
		$this->log->info("GUSER LOGIN SUCCESS $username $remote_addr");

		$user = $this->em->getRepository("GuserBundle:User")->findOneByUsername($username);
		if($user) {
			$user->setFailedLoginCount(0);
			$this->em->flush();
		}
	}
	public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event) {
		$username = $event->getAuthenticationToken()->getUser();
		$remote_addr = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
		$this->log->info("GUSER LOGIN FAILED: $username $remote_addr");

		$user = $this->em->getRepository("GuserBundle:User")->findOneByUsername($username);
		if($user) {
			$user->setFailedLoginCount($user->getFailedLoginCount()+1);
			if($user->getFailedLoginCount() >= User::FAILED_LOGIN_LOCKOUT) {
				$user->setLocked(true);
			}
			$this->em->flush();
		}
	}
}
