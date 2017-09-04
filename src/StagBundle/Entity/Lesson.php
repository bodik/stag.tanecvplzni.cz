<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
	 * @Assert\Range(min = 0)
	 */
	private $length;

	/**
	 * @ORM\Column(name="level", type="string", length=1024, nullable=true)
	 */
	private $level;

	/**
	 * @ORM\Column(name="lecturer", type="string", length=1024, nullable=true)
	 */
	private $lecturer;
	
	/**
	 * @ORM\Column(name="description", type="string", length=255, nullable=true)
	 */
	private $description;


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
	
	public function getLevel() { return $this->level; }
	public function setLevel($level) { $this->level = $level; return $this; }

	public function getLecturer() { return $this->lecturer; }
	public function setLecturer($lecturer) { $this->lecturer = $lecturer; return $this; }

	public function getDescription() { return $this->description; }
	public function setDescription($description) { $this->description = $description; return $this; }
	
	public function getCourseRef() { return $this->courseRef; }
	public function setCourseRef($courseRef) { $this->courseRef = $courseRef; return $this; }	
	
}