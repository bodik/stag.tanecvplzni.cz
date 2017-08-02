<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
	 * @Route("/course/list", name="course_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
        	$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		return $this->render("StagBundle:Course:list.html.twig", [ "courses" => $courses ] );
	}


	
	/**
	 * @Route("/course/add", name="course_add")
	 * @Security("has_role('ROLE_ADMIN')")
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
	 * @Security("has_role('ROLE_ADMIN')")
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
	 * @Security("has_role('ROLE_ADMIN')")
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



	/**
	 * @Route("/course/grid", name="course_grid")
	 */
	public function gridAction(Request $request) {
		$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		$data = [];
		foreach ($courses as $tmp) {
			$oneLesson = $tmp->getLessons()[0];
			if($oneLesson) {			
				$currentLocale = setlocale(LC_TIME, 0);
				setlocale(LC_TIME, 'cs_CZ.UTF-8');
				$day = strtolower(strftime('%A', $oneLesson->getTime()->getTimestamp()));
				setlocale(LC_TIME, $currentLocale);
				$begin = $oneLesson->getTime()->format('H:i');
				$end = $oneLesson->getTime()->add(new \DateInterval("PT".$oneLesson->getLength()."M"))->format('H:i');
				$timespan = "{$day} {$begin} - {$end}";
			} else {
				$timespan = "";
			}
			
			$data[] = [
				"id" => $tmp->getId(),
				"name" => $tmp->getName(),
				"level" => $tmp->getLevel(),
				"teacher" => $tmp->getTeacher(),
				"place" => $tmp->getPlace(),
				"color" => $tmp->getColor(),
				"timespan" => $timespan,
			];
		}		
		
		return $this->render("StagBundle:Course:grid.html.twig", [ "courses" => $data ] );
	}
	
}
