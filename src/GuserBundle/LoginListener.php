<?php

namespace GuserBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener {
	private $log;
	private $container;
	
	public function __construct(LoggerInterface $log, ContainerInterface $container) {
		$this->log = $log;
		$this->container = $container;
	}

	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
		$username = $event->getAuthenticationToken()->getUser()->getUsername();
		$remote_addr = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
		$this->log->info("GUSER LOGIN SUCCESS $username $remote_addr");
	}
	public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event) {
		$user = $event->getAuthenticationToken()->getUser();
		$remote_addr = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
		$this->log->info("GUSER LOGIN FAILED: $user $remote_addr");
	}
}
