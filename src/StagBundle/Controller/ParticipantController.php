<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Participant;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Form\ParticipantType;
use StagBundle\Form\ParticipantApplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ParticipantController extends Controller {
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/participant/list", name="participant_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
        	$participants = $this->em->getRepository("StagBundle:Participant")->findAll();
		return $this->render("StagBundle:Participant:index.html.twig", [ "participants" => $participants ] );
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
			//$participant->setDomain($participant->getCourseRef()->getCourse());
			
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
			if (!empty($participant)) {
				$this->em->remove($participant);
				$this->em->flush();

				$this->addFlash("success","Participant {$participant->getSn()} was deleted");
			} else {
				$this->addFlash("error","Participant with ID {$id} does not exits");
			}
			return $this->redirectToRoute("participant_list");
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}
	
	
	
	
	/**
	 * @Route("/participant/application/{course_id}", name="participant_application", defaults={"course_id" = null})
	 */
	public function applicationAction(Request $request, $course_id) {
		$participant = new Participant();
		$participant->setCourseRef($this->em->getRepository("StagBundle:Course")->findOneById($course_id));
		$form = $this->createForm(ParticipantApplicationType::class, $participant);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($form->get('tosagreed')->getData() == 1) {
				$participant = $form->getData();
				$this->em->persist($participant);
				$this->em->flush();

				$this->addFlash("success", "Vaše přihláška byla přijata");
				return $this->redirectToRoute("default_index");
			} else {
				$this->addFlash("success", "Musíte souhlasit ...");
			}
		}

		return $this->render("StagBundle:Participant:application.html.twig", array("form" => $form->createView(),));
	}

}
