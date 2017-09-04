<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Lesson;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Form\LessonType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class LessonController extends Controller {
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/lesson/list", name="lesson_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
        	$lessons = $this->em->getRepository("StagBundle:Lesson")->findAll();
		return $this->render("StagBundle:Lesson:list.html.twig", [ "lessons" => $lessons ] );
	}


	
	/**
	 * @Route("/lesson/add/{course_id}", name="lesson_add", defaults={"course_id" = null})
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addAction(Request $request, $course_id) {
		$lesson = new Lesson();
		$lesson->setCourseRef($this->em->getRepository("StagBundle:Course")->findOneById($course_id));
		$form = $this->createForm(LessonType::class, $lesson);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$lesson = $form->getData();
			
			$this->em->persist($lesson);
			$this->em->flush();

			$this->addFlash("success","Lesson {$lesson->getId()} was created");
			return $this->redirectToRoute("course_book",["id" => $lesson->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle:Lesson:addedit.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/lesson/edit/{id}", name="lesson_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, $id) {
		$lesson = $this->em->getRepository("StagBundle:Lesson")->find($id);
		$form = $this->createForm(LessonType::class, $lesson);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$lesson = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Lesson {$lesson->getId()} was saved");
			return $this->redirectToRoute("course_book",["id" => $lesson->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle:Lesson:addedit.html.twig", array("form" => $form->createView(),));
	}	
	
	
		
	/**
	 * @Route("/lesson/delete/{id}", name="lesson_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		$lesson = $this->em->getRepository("StagBundle:Lesson")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $lesson,
			array("action" => $this->generateUrl("lesson_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($lesson) {
				$this->em->remove($lesson);
				$this->em->flush();

				$this->addFlash("success","Lesson {$lesson->getId()} was deleted");
			} else {
				$this->addFlash("error","Lesson with ID {$id} does not exits");
			}
			return $this->redirectToRoute("course_book",["id" => $lesson->getCourseRef()->getId()]);
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}
	
	
	
	
	/**
	 * @Route("/lesson/calendar", name="lesson_calendar")
	 */
	public function calendarAction(Request $request) {
		return $this->render("StagBundle:Lesson:calendar.html.twig");
	}
	/**
	 * @Route("/lesson/events", name="lesson_events")
	 */
	public function eventsAction(Request $request) {
		$data = [];
		$lessons = $this->em->getRepository("StagBundle:Lesson")->findAll();
		foreach ($lessons as $tmp) {
			#{'title': 'Meeting','start': '2017-05-12T14:30:00'}
			$data[] = [
				"title" => $tmp->getCourseRef()->getName(),
				"start" => $tmp->getTime()->format('c'),
				"end" => $tmp->getTime()->add(new \DateInterval("PT".$tmp->getLength()."M"))->format('c'),
				"color" => $tmp->getCourseRef()->getColor(),
				"place" => $tmp->getCourseRef()->getPlace(),
				"url" => $this->generateUrl('course_show', ["id" => $tmp->getCourseRef()->getId()]),
			];
		}

		return new JsonResponse($data);
	}
	
	

}
