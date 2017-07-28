<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use StagBundle\Entity\Lesson;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Form\LessonType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LessonController extends Controller {
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/lesson", name="lesson_list")
	 */
	public function listAction(Request $request) {
        	$lessons = $this->em->getRepository("StagBundle:Lesson")->findAll();
		return $this->render("StagBundle:Lesson:index.html.twig", [ "lessons" => $lessons ] );
	}


	
	/**
	 * @Route("/lesson/add/{course_id}", name="lesson_add", defaults={"course_id" = null})
	 */
	public function addAction(Request $request, $course_id) {
		$lesson = new Lesson();
		$lesson->setCourseRef($this->em->getRepository("StagBundle:Course")->findOneById($course_id));
		$form = $this->createForm(LessonType::class, $lesson);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$lesson = $form->getData();
			//$participant->setDomain($participant->getCourseRef()->getCourse());
			
			$this->em->persist($lesson);
			$this->em->flush();

			$this->addFlash("success","Lesson {$lesson->getId()} was created");
			return $this->redirectToRoute("lesson_list");
		}

		return $this->render("StagBundle:Lesson:addedit.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/lesson/edit/{id}", name="lesson_edit")
	 */
	public function editAction(Request $request, $id) {
		$lesson = $this->em->getRepository("StagBundle:Lesson")->find($id);
		$form = $this->createForm(LessonType::class, $lesson);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$lesson = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Lesson {$lesson->getId()} was saved");
            		return $this->redirectToRoute("lesson_list");
		}

		return $this->render("StagBundle:Lesson:addedit.html.twig", array("form" => $form->createView(),));
	}	
	
	
		
	/**
	 * @Route("/lesson/delete/{id}", name="lesson_delete")
	 */
	public function deleteAction(Request $request, $id) {
		$lesson = $this->em->getRepository("StagBundle:Lesson")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $lesson,
			array("action" => $this->generateUrl("lesson_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if (!empty($lesson)) {
				$this->em->remove($lesson);
				$this->em->flush();

				$this->addFlash("success","Lesson {$lesson->getId()} was deleted");
			} else {
				$this->addFlash("error","Lesson with ID {$id} does not exits");
			}
			return $this->redirectToRoute("lesson_list");
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}

}
