<?php

namespace StagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller {
	public function indexAction() {
		return $this->render('StagBundle:Default:index.html.twig');
	}


	/**
	 * @Route("/help", name="default_help")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function helpAction() {
		return $this->render('StagBundle:Default:help.html.twig');
	}
}
