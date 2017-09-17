<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Ticket;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Form\TicketActiveButtonType;
use StagBundle\Form\TicketType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class TicketController extends Controller {
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/ticket/list", name="ticket_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
        	$tickets = $this->em->getRepository("StagBundle:Ticket")->findAll();
		return $this->render("StagBundle:Ticket:list.html.twig", [ "tickets" => $tickets ] );
	}


	
	/**
	 * @Route("/ticket/add/{course_id}", name="ticket_add", defaults={"course_id" = null})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addAction(Request $request, $course_id) {
		$ticket = new Ticket();
		$ticket->setCourseRef($this->em->getRepository("StagBundle:Course")->findOneById($course_id));
		$form = $this->createForm(TicketType::class, $ticket);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$ticket = $form->getData();
			
			$this->em->persist($ticket);
			$this->em->flush();

			$this->addFlash("success","Ticket {$ticket->getId()} was created");
			return $this->redirectToRoute("course_book",["id" => $ticket->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle:Ticket:addedit.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/ticket/edit/{id}", name="ticket_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, $id) {
		$ticket = $this->em->getRepository("StagBundle:Ticket")->find($id);
		$form = $this->createForm(TicketType::class, $ticket);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$ticket = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Ticket {$ticket->getId()} was saved");
			return $this->redirectToRoute("course_book",["id" => $ticket->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle:Ticket:addedit.html.twig", array("form" => $form->createView(),));
	}	
	
	
		
	/**
	 * @Route("/ticket/delete/{id}", name="ticket_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		$ticket = $this->em->getRepository("StagBundle:Ticket")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $ticket,
			array("action" => $this->generateUrl("ticket_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($ticket) {
				$this->em->remove($ticket);
				$this->em->flush();

				$this->addFlash("success","Ticket {$ticket->getId()} was deleted");
			} else {
				$this->addFlash("error","Ticket with ID {$id} does not exits");
			}
			return $this->redirectToRoute("course_book",["id" => $ticket->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}


	/**
	 * @Route("/ticket/active/{id}", name="ticket_active")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function activeAction(Request $request, $id) {
		$ticket = $this->em->getRepository("StagBundle:Ticket")->find($id);
		$form = $this->createForm(TicketActiveButtonType::class, $ticket,
			array("action" => $this->generateUrl("ticket_active", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if($ticket) {
				$ticket->setActive( !$ticket->getActive() );
				$this->em->flush();

				$this->addFlash("success", "Ticket {$ticket->getName()} active toggle.");
			} else {
				$this->addFlash("error","Ticket with ID {$id} does not exits");
			}

			return $this->redirect($request->server->get('HTTP_REFERER'));
		}

		return $this->render("StagBundle:Ticket:activebutton.html.twig", ["form" => $form->createView(), "ticket" => $ticket]);
	}
}
