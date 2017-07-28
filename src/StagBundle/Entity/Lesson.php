<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lesson
 *
 * @ORM\Table(name="lesson")
 * @ORM\Entity(repositoryClass="StagBundle\Repository\LessonRepository")
 */
class Lesson {
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;


	/**
	 * @ORM\Column(name="time", type="datetime")
	 */
	private $time;
	
	/**
	 * @ORM\Column(name="length", type="integer")
	 */
	private $length;
	
	/**
	 * @ORM\Column(name="note", type="string", length=255, nullable=true)
	 */
	private $note;


	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="lessons")
	 * @ORM\JoinColumn(name="course_id", referencedColumnName="id", nullable=false)
	 */
	private $courseRef;
	
	
	public function __construct() {
	}


	public function getId() { return $this->id; }

	public function getTime() { return $this->time; }
	public function setTime($time) { $this->time = $time; return $this; }

	public function getLength() { return $this->length; }
	public function setLength($length) { $this->length = $length; return $this; }
	
	public function getNote() { return $this->note; }
	public function setNote($note) { $this->note = $note; return $this; }
	
	public function getCourseRef() { return $this->courseRef; }
	public function setCourseRef($courseRef) { $this->courseRef = $courseRef; return $this; }	
	
}