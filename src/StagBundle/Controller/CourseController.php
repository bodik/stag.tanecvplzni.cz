<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Course;
use StagBundle\Entity\Lesson;
use StagBundle\Form\CourseScheduleType;
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
		$blobs = $this->em->getRepository("StagBundle:Blob")->findAll();
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

		return $this->render("StagBundle:Course:addedit.html.twig", ["form" => $form->createView(), "blobs" => $blobs]);
	}



	/**
	 * @Route("/course/edit/{id}", name="course_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		$blobs = $this->em->getRepository("StagBundle:Blob")->findAll();
		$form = $this->createForm(CourseType::class, $course);
		

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$course = $form->getData();
			$this->em->flush();

			$this->addFlash("success","Course {$course->getName()} was saved");
            		return $this->redirectToRoute("course_list");
		}

		return $this->render("StagBundle:Course:addedit.html.twig", ["form" => $form->createView(), "blobs" => $blobs]);
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
			if ($course) {
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
	 * @Route("/course/schedule/{id}", name="course_schedule")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function scheduleAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		
		$schedule = [];
		foreach ($course->getLessons() as $tmp) { $schedule[] = $tmp->getTime()->format('c'); }
		asort($schedule);
		$form = $this->createForm(CourseScheduleType::class, ["schedule" => join("\n", $schedule)]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			$newLessons = [];
			foreach ( explode("\n", $data["schedule"]) as $date ) {
				$tmp = new Lesson();
				$tmp->setLength($data["length"]);
				$tmp->setNote($data["note"]);
				$tmp->setTime(new \Datetime($date));
				$newLessons[] = $tmp;
			}
			foreach ( $course->getLessons() as $tmp ) {
				$this->em->remove($tmp);
			}
			foreach ( $newLessons as $tmp ) {
				$tmp->setCourseRef($course);
				$this->em->persist($tmp);
			}
			$this->em->flush();
			
			$this->addFlash("success","Course {$course->getName()} was scheduled");
			return $this->redirectToRoute("course_book", ["id" => $course->getId()]);
		}

		return $this->render("StagBundle:Course:schedule.html.twig", ["form" => $form->createView(), "course" => $course]);
	}



	/**
	 * @Route("/course/book/{id}", name="course_book")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function Action(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		return $this->render("StagBundle:Course:book.html.twig", ["course" => $course]);
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
	 * @Route("/", name="default_index")
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
				"pictureRef" => $tmp->getPictureRef(),
			];
		}		
		
		return $this->render("StagBundle:Course:grid.html.twig", [ "courses" => $data ] );
	}
	
}
