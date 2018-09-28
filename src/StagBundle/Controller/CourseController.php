<?php

namespace StagBundle\Controller;

use Box\Spout\Common\Type;;
use Box\Spout\Writer\WriterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Course;
use StagBundle\Entity\Lesson;
use StagBundle\Form\CourseActiveButtonType;
use StagBundle\Form\CourseScheduleType;
use StagBundle\Form\CourseType;
use StagBundle\Form\DeleteButtonType;
use StagBundle\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends Controller {
	private $em;
	private $payment;


	public function __construct(
	    EntityManagerInterface $em,
        PaymentService $payment
    ) {
		$this->em = $em;
		$this->payment = $payment;
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
				foreach ($course->getTickets() as $ticket) {
					foreach ($ticket->getParticipants() as $participant) {
						$this->em->remove($participant);
					}
					$this->em->remove($ticket);
				}
				foreach ($course->getLessons() as $lesson) {
					$this->em->remove($lesson);
				}
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
		foreach ($course->getLessons() as $tmp) { $schedule[] = $tmp->getTime()->format('d.m.Y H:i'); }
		asort($schedule);
		$form = $this->createForm(CourseScheduleType::class, ["schedule" => join("\n", $schedule)]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			
			# validate schedule and update
			$newLessons = $this->_parseSchedule($data);
			if($newLessons) {
				foreach ( $course->getLessons() as $tmp ) { $this->em->remove($tmp); }
				foreach ( $newLessons as $tmp ) {
					$tmp->setCourseRef($course);
					$this->em->persist($tmp);
				}
				$this->em->flush();
				$this->addFlash("success","Course {$course->getName()} was scheduled");
				return $this->redirectToRoute("course_manage", ["id" => $course->getId()]);
			} else {
				$this->addFlash("error","Course schedule not valid");
			}
		}

		return $this->render("StagBundle:Course:schedule.html.twig", ["form" => $form->createView(), "course" => $course]);
	}

	public function _parseSchedule($data) {
		$newLessons = [];
		foreach ( explode("\n", $data["schedule"]) as $date ) {
			$tmp = new Lesson();
			$tmp->setLength($data["length"]);
			$t = \DateTime::createFromFormat('d.m.Y H:i',trim($date));
			if($t) { $tmp->setTime($t); } else { return null; } # not so nice, but works for validation
			$newLessons[] = $tmp;
		}
		asort($newLessons);
		return $newLessons;
	}



	/**
	 * @Route("/course/manage/{id}", name="course_manage")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function manageAction(Request $request, $id) {
		
		//CAVEAT: participants list outofmemory in profiler during dev stage, probably by participant.ticketRef.courseRef.ticket_id cycle dump in twig
		//https://stackoverflow.com/questions/30229637/out-of-memory-error-in-symfony
		if ($this->container->has('profiler')) { $this->container->get('profiler')->disable(); }

		$course = $this->em->getRepository("StagBundle:Course")->find($id);
        $this->_checkCourseParticipantsPayments($course);

        if (!empty($request->get('event')) && $request->get('event') === 'paymentUpdates') {
            $this->addFlash($request->get('type'), $request->get('message'));
            return $this->redirect($request->getUri());
        }

		return $this->render("StagBundle:Course:manage.html.twig", ["course" => $course]);
	}



	/**
	 * @Route("/course/suggest/place", name="course_suggest_place")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function suggestPlaceAction(Request $request) {
		$data = [];
		$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		foreach ($courses as $course) {
			array_push($data, ["value" => $course->getPlace()]);
		}
		return new JsonResponse($data);
	}



	/**
	 * @Route("/course/suggest/lecturer", name="course_suggest_lecturer")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function suggestLecturerAction(Request $request) {
		$data = [];
		$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		foreach ($courses as $course) {
			array_push($data, ["value" => $course->getLecturer()]);
		}
		return new JsonResponse($data);
	}



	/**
	 * @Route("/course/menulist", name="course_menulist")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function menuListAction(Request $request) {
        	$courses = $this->em->getRepository("StagBundle:Course")->findAll();
		return $this->render("StagBundle:Course:menulist.html.twig", [ "courses" => $courses ] );
	}



	/**
	 * @Route("/course/active/{id}", name="course_active")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function activeAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);
		$form = $this->createForm(CourseActiveButtonType::class, $course,
			array("action" => $this->generateUrl("course_active", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if($course) {
				$course->setActive( !$course->getActive() );
				$this->em->flush();

				$this->addFlash("success", "Course {$course->getName()} active toggle.");
			} else {
				$this->addFlash("error","Course with ID {$id} does not exits");
			}

			return $this->redirect($request->server->get('HTTP_REFERER'));
		}

		return $this->render("StagBundle:Course:activebutton.html.twig", ["form" => $form->createView(), "course" => $course]);
	}



	/**
	 * @Route("/course/export/{id}", name="course_export")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function exportAction(Request $request, $id) {
		$course = $this->em->getRepository("StagBundle:Course")->find($id);

        $this->_checkCourseParticipantsPayments($course);

		#TODO: convert to single aggregated query, next time
		$females = 0; $males = 0;
		foreach($course->getTickets() as $ticket) {
			foreach($ticket->getParticipants() as $participant) {
				switch ($participant->getGender()) {
					case "female": $females++; break;
					case "male": $males++; break;
				}
			}
		}
		$fm = $females+$males;

		$data[] = ["Kurz", $course->getName()];
		$data[] = ["Typ", $course->getType()];
		$data[] = ["Úroveň", $course->getLevel()];
		$data[] = ["Lektor", $course->getLecturer()];
		$data[] = ["Místo", $course->getPlace()];
		$data[] = ["Platební informace", $course->getPaymentInfo()];
		$data[] = ["FB událost", $course->getFbEventUrl()];
		$data[] = ["FB skupina", $course->getFbGroupUrl()];
		$data[] = ["Aktivní", $course->getActive()];
		$data[] = ["Obsazenost ženy + muži = celkem", "{$females}+{$males} = {$fm}"];
		$data[] = [""];
		$data[] = [
			"Id", "Jméno", "Přijmení", "Email",
			"Telefon", "Pohlaví", "Partner", "Reference",
			"Poznámka", "Vstup", "Záloha", "Platba", "VS platby",
			"Vytvořeno", "Cena vstupu"
		];
		foreach ($course->getTickets() as $ticket) {
			foreach ($ticket->getParticipants() as $participant) {
				$data[] = [
					$participant->getId(), $participant->getGn(), $participant->getSn(), $participant->getEmail(),
					$participant->getPhoneNumber(), $participant->getGender(), $participant->getPartner(), $participant->getReference(),
					$participant->getNote(), $participant->getTicketRef()->getName(), $participant->getDeposit(), $participant->getPayment(), $participant->getPaymentReferenceNumber(),
					$participant->getCreated()->format('d.m.Y H:i'), $ticket->getPrice()
				];
			}
		}
		$filePath = tempnam(sys_get_temp_dir(), "stagtvp_export_");
		$writer = WriterFactory::create(Type::XLSX);
		$writer->openToFile($filePath);
		foreach($data as $row) { $writer->addRow($row); }
		$writer->close();

		$response = new BinaryFileResponse($filePath);
		$response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($course->getName()).'.xlsx');
		$response->deleteFileAfterSend(true);
		return $response;
	}




	/**
	 * @Route("/course/show/{id}", name="course_show")
	 */
	public function showAction(Request $request, $id) {

		$course = $this->em->getRepository("StagBundle:Course")->findOneById($id);
		if ( !$course ) { throw $this->createNotFoundException(); }

		# deny non-active courses to non-admin user
		if (
			( ($course->getActive() == false) ) &&
			( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') )
		) { throw $this->createAccessDeniedException(); }

		return $this->render("StagBundle:Course:show.html.twig", ["course" => $course]);
	}



	/**
	 * @Route("/course/grid/{type}", name="course_grid", defaults={"type" = null})
	 * @Route("/", name="default_index", defaults={"type" = null})
	 */
	public function gridAction(Request $request, $type) {
		$query = [];

		# deny non-active courses to non-admin user
		if ( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ) { $query["active"] = true; }

		if ($type) { $query["type"] = $type; }
		$courses = $this->em->getRepository("StagBundle:Course")->findBy($query);
		
		$data = [];
		foreach ($courses as $tmp) {
			$timespan = "";
			switch ( $tmp->getType() ) {
				default:
					$timespan = $this->_timespanRegular($tmp);
					break;
				case "workshop":
					$timespan = $this->_timespanWorkshop($tmp);
					break;
				case "party":
					if ( count($tmp->getLessons()) == 1 ) {
						$timespan = $this->_timespanWorkshop($tmp);
					} else {
						$timespan = $this->_timespanRegular($tmp);
					}
					break;
			}

			$data[] = [
				"id" => $tmp->getId(),
				"name" => $tmp->getName(),
				"type" => $tmp->getType(),
				"level" => $tmp->getLevel(),
				"lecturer" => $tmp->getLecturer(),
				"place" => $tmp->getPlace(),
				"color" => $tmp->getColor(),
				"timespan" => $timespan,
				"pictureRef" => $tmp->getPictureRef(),
				"fbEventUrl" => $tmp->getFbEventUrl(),
				"fbGroupUrl" => $tmp->getFbGroupUrl(),
				"active" => $tmp->getActive(),
			];
		}	
		
		return $this->render("StagBundle:Course:grid.html.twig", [ "courses" => $data ] );
	}
	
	public function _timespanRegular($course) {
		$timespan = "";
		
		$oneLesson = $course->getLessons()[0];
		if($oneLesson) {
			# get day in czech locale
			$currentLocale = setlocale(LC_TIME, 0);
			setlocale(LC_TIME, 'cs_CZ.UTF-8');
			$day = strtolower(strftime('%A', $oneLesson->getTime()->getTimestamp()));
			setlocale(LC_TIME, $currentLocale);
				
			$begin = $oneLesson->getTime()->format('H:i');
			$end = $oneLesson->getTime()->add(new \DateInterval("PT".$oneLesson->getLength()."M"))->format('H:i');
			$timespan = "{$day} {$begin} - {$end}";
		}
		return $timespan;
	}
	
	public function _timespanWorkshop($course) {
		$timespan = "";
		
		$firstLesson = $course->getLessons()[0];
		$lastLesson = $course->getLessons()[count($course->getLessons())-1];
		if ( !empty($firstLesson) && !empty($lastLesson) ) {
			$begin = $firstLesson->getTime()->format('d.m.Y H:i');
			$end = $lastLesson->getTime()->add(new \DateInterval("PT".$lastLesson->getLength()."M"))->format('d.m.Y H:i');
			$timespan = "{$begin} - {$end}";
		}
		
		return $timespan;
	}

    private function _checkCourseParticipantsPayments($course)
    {
        $this->payment->_setParameters($this->container->getParameter('payment_api_url'), $this->container->getParameter('cacert_path'));
        $participants = array();
        if (!empty($course->getTickets())) {
            foreach ($course->getTickets() as $ticket) {
                foreach ($ticket->getParticipants() as $participant) {
                    array_push($participants, $participant);
                }
            }
        }
        $this->payment->checkTicketParticipantsPayments($participants, $this->em);
    }

}
