<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Participant;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Form\ParticipantApplicationType;
use StagBundle\Form\ParticipantDepositPaymentButtonType;
use StagBundle\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ParticipantController extends Controller {
	private $em;
	private $appName;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		$this->appName = (array_key_exists("SERVER_NAME", $_SERVER) ? $_SERVER["SERVER_NAME"] : "localhost");
	}



	/**
	 * @Route("/participant/list", name="participant_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
		
		//CAVEAT: participants list outofmemory in profiler during dev stage, probably by participant.ticketRef.courseRef.ticket_id cycle dump in twig
		//https://stackoverflow.com/questions/30229637/out-of-memory-error-in-symfony
		if ($this->container->has('profiler')) { $this->container->get('profiler')->disable(); }

        	$participants = $this->em->getRepository("StagBundle:Participant")->findAll();
		return $this->render("StagBundle:Participant:list.html.twig", [ "participants" => $participants ] );
	}


	
	/**
	 * @Route("/participant/add", name="participant_add")
	 * @Security("has_role('ROLE_ADMIN')")	 
	 */
	public function addAction(Request $request) {
		$participant = new Participant();
		$form = $this->createForm(ParticipantType::class, $participant);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$participant = $form->getData();
			
			$this->em->persist($participant);
			$this->em->flush();

			$this->addFlash("success","Participant {$participant->getSn()} was created");
			return $this->redirectToRoute("participant_list");
		}

		return $this->render("StagBundle:Participant:addedit.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/participant/edit/{id}", name="participant_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, $id) {
		$participant = $this->em->getRepository("StagBundle:Participant")->find($id);
		$form = $this->createForm(ParticipantType::class, $participant);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$participant = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Participant {$participant->getSn()} was saved");
            		return $this->redirectToRoute("participant_list");
		}

		return $this->render("StagBundle:Participant:addedit.html.twig", array("form" => $form->createView(),));
	}	
	
	
		
	/**
	 * @Route("/participant/delete/{id}", name="participant_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		$participant = $this->em->getRepository("StagBundle:Participant")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $participant,
			array("action" => $this->generateUrl("participant_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($participant) {
				$this->em->remove($participant);
				$this->em->flush();

				$this->addFlash("success","Participant {$participant->getSn()} was deleted from {$participant->getTicketRef()->getCourseRef()->getName()}");
			} else {
				$this->addFlash("error","Participant with ID {$id} does not exits");
			}
			return $this->redirect($request->server->get('HTTP_REFERER'));
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/participant/deposit/{id}/{value}", name="participant_deposit", defaults={"value" = null})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function depositAction(Request $request, $id, $value) {
		$participant = $this->em->getRepository("StagBundle:Participant")->find($id);
		$form = $this->createForm(ParticipantDepositPaymentButtonType::class, $participant,
			array("action" => $this->generateUrl("participant_deposit", ["id" => $id, "value" => $value]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if($participant) {
				$participant->setDeposit($value);
				$this->em->flush();

				$this->addFlash("success", "Participant {$participant->getSn()} deposit saved.");
			} else {
				$this->addFlash("error","Participant with ID {$id} does not exits");
			}

			return $this->redirect($request->server->get('HTTP_REFERER'));
		}

		return $this->render("StagBundle:Participant:depositpaymentbutton.html.twig", ["form" => $form->createView(), "value" => $value]);
	}

	/**
	 * @Route("/participant/payment/{id}/{value}", name="participant_payment", defaults={"value" = null})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function paymentAction(Request $request, $id, $value) {
		$participant = $this->em->getRepository("StagBundle:Participant")->find($id);
		$form = $this->createForm(ParticipantDepositPaymentButtonType::class, $participant,
			array("action" => $this->generateUrl("participant_payment", ["id" => $id, "value" => $value]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if($participant) {
				$participant->setPayment($value);
				$this->em->flush();

				$this->addFlash("success", "Participant {$participant->getSn()} payment saved.");
			} else {
				$this->addFlash("error","Participant with ID {$id} does not exits");
			}

			return $this->redirect($request->server->get('HTTP_REFERER'));
		}

		return $this->render("StagBundle:Participant:depositpaymentbutton.html.twig", ["form" => $form->createView(), "value" => $value]);
	}






	/**
	 * @Route("/participant/application/{ticket_id}", name="participant_application", defaults={"ticket_id" = null})
	 */
	public function applicationAction(Request $request, $ticket_id) {
		$participant = new Participant();
		$participant->setTicketRef($this->em->getRepository("StagBundle:Ticket")->findOneById($ticket_id));
		$form = $this->createForm(ParticipantApplicationType::class, $participant);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($form->get('tosagreed')->getData() == 1) {
				$participant = $form->getData();
				$this->em->persist($participant);
				$this->em->flush();

				$this->_sendApplicationAcceptedEmail($participant);

				$this->addFlash("success", "Vaše přihláška byla přijata");
				return $this->render("StagBundle:Participant:applicationAccepted.html.twig", ["participant" => $participant]);
			} else {
				$this->addFlash("success", "Musíte souhlasit ...");
			}
		}

		return $this->render("StagBundle:Participant:application.html.twig", ["form" => $form->createView(), "course" => $participant->getTicketRef()->getCourseRef(), ]);
	}
	
	public function _sendApplicationAcceptedEmail($participant) {
		# send email
		$message = (new \Swift_Message("{$this->appName}: Přihláška č. {$participant->getId()} (kurz {$participant->getTicketRef()->getCourseRef()->getName()} - {$participant->getTicketRef()->getName()}) byla přijata"));
		$message->setFrom( ($this->container->getParameter('mailer_user') ? $this->container->getParameter('mailer_user') : "noreply@{$this->appName}") );
		$message->setReplyTo("info@tanecvplzni.cz");
		$message->setBcc("info@tanecvplzni.cz");

		$message->setTo($participant->getEmail());
		$text = $participant->getTicketRef()->getCourseRef()->getApplEmailText();
		$text .= $this->renderView("StagBundle:Participant:applicationAcceptedEmailFooter.html.twig", ["participant" => $participant]);
		$message->setBody($text, "text/plain");

		$this->get("mailer")->send($message);

		return;

	}
}
