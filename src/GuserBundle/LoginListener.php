<?php

namespace GuserBundle;

use Doctrine\ORM\EntityManagerInterface;
use GuserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


class LoginListener {
	private $log;
	private $container;
	private $em;
	private $appName;
	
	public function __construct(LoggerInterface $log, ContainerInterface $container, EntityManagerInterface $em) {
		$this->log = $log;
		$this->em = $em;
		$this->container = $container;
		$this->appName = (array_key_exists("SERVER_NAME", $_SERVER) ? $_SERVER["SERVER_NAME"] : "localhost");
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
		$this->log->info("GUSER LOGIN FAILED $username $remote_addr");

		$user = $this->em->getRepository("GuserBundle:User")->findOneByUsername($username);
		if($user) {
			$user->setFailedLoginCount($user->getFailedLoginCount()+1);
			if($user->getFailedLoginCount() >= User::FAILED_LOGIN_LOCKOUT) {
				$user->setLocked(true);
				$this->log->info("GUSER USER LOCKEDACCOUNT $username $remote_addr");
				$this->_sendAccountLockedEmail($user);
			}
			$this->em->flush();
		}
	}
	
	public function _sendAccountLockedEmail($user) {
		$message = (new \Swift_Message("$this->appName: {$user->getUsername()} account locked"));
		$message->setFrom("noreply@{$this->appName}");
		$message->setTo($user->getEmail());
		$message->setBody(
			$this->container->get('templating')->render("GuserBundle:User:accountlockedemail.html.twig", [
				"appname" => $this->appName,
				"username" => $user->getUsername(),
				"email" => $user->getEmail(),
				"url" => $this->container->get('router')->generate("user_lostpassword", [], UrlGeneratorInterface::ABSOLUTE_URL)
				]),
			"text/plain"
		);
		$this->container->get("mailer")->send($message);

		return;
	}
}
