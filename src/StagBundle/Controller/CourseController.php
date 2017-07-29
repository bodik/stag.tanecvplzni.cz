<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use StagBundle\Entity\Course;
use StagBundle\Form\CourseType;
use StagBundle\Form\DeleteButtonType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseController extends Controller {
	private $em;


	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/course", name="course_list")
	 */
	public function listAction(Request $request) {
        	$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		return $this->render("StagBundle:Course:index.html.twig", [ "courses" => $courses ] );
	}


	
	/**
	 * @Route("/course/add", name="course_add")
	 */
	public function addAction(Request $request) {
		$course = new Course();
		$form = $this->createForm(CourseType::class, $course);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			
			//TODO: test to inject false created, modified ?
			$course = $form->getData();
			$this->em->persist($course);
			$this->em->flush();

			$this->addFlash("success","Course {$course->getName()} was created");
			return $this->redirectToRoute("course_list");
		}

		return $this->render("StagBundle:Course:addedit.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/course/edit/{id}", name="course_edit")
	 */
	public function editAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		$form = $this->createForm(CourseType::class, $course);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$course = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Course {$course->getName()} was saved");
            		return $this->redirectToRoute("course_list");
		}

		return $this->render("StagBundle:Course:addedit.html.twig", array("form" => $form->createView(),));
	}	
	
	
		
	/**
	 * @Route("/course/delete/{id}", name="course_delete")
	 */
	public function deleteAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $course,
			array("action" => $this->generateUrl("course_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if (!empty($course)) {
				$this->em->remove($course);
				$this->em->flush();

				$this->addFlash("success","Course {$course->getName()} was deleted");
			} else {
				$this->addFlash("error","Course with ID {$id} does not exits");
			}
			return $this->redirectToRoute("course_list");
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/course/price/{id}/{paired}", name="course_price")
	 */
	public function priceAction(Request $request, $id, $paired) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		if ( $paired == "single" ) {
			return new Response("{$course->getPriceSingle()},-");
		} elseif ($paired == "pair") {
			return new Response("{$course->getPricePair()},-");
		} else {
			return new Response("error");
		}
	}
	
	
	/**
	 * @Route("/course/show/{id}", name="course_show")
	 */
	public function showAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		return $this->render("StagBundle:Course:show.html.twig", ["course" => $course]);
	}
	
}
