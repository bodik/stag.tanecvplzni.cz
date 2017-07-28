<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Course
 *
 * @ORM\Table(name="course")
 * @ORM\Entity(repositoryClass="StagBundle\Repository\CourseRepository")
 */
class Course {
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;

	/**
	 * @ORM\Column(name="description", type="string", length=65535, nullable=true)
	 */
	private $description;

	/**
	 * @ORM\Column(name="teacher", type="string", length=255)
	 */
	private $teacher;

	/**
	 * @ORM\Column(name="place", type="string", length=255)
	 */
	private $place;

	/**
	 * @ORM\Column(name="capacity", type="integer")
	 */
	private $capacity;

	/**
	 * @var bool
	 *
	 * @ORM\Column(name="pair", type="boolean")
	 */
	private $pair;

	/**
	 * @ORM\Column(name="price_single", type="integer")
	 */
	private $priceSingle;

	/**
	 * @ORM\Column(name="price_pair", type="integer")
	 */
	private $pricePair;
	
	/**
	 * @ORM\OneToMany(targetEntity="Lesson", mappedBy="courseRef")
	 */
	private $lessons;



	public function __construct() {
		$this->pair = false;
		$this->lessons = new ArrayCollection();
	}

	public function getId() { return $this->id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; return $this; }

	public function getDescription() { return $this->description; }
	public function setDescription($description) { $this->description = $description; return $this; }

	public function getTeacher() { return $this->teacher; }
	public function setTeacher($teacher) { $this->teacher = $teacher; return $this; }

	public function getPlace() { return $this->place; }
	public function setPlace($place) { $this->place = $place; return $this; }

	public function getCapacity() { return $this->capacity; }
	public function setCapacity($capacity) { $this->capacity = $capacity; return $this; }

	public function getPair() { return (bool) $this->pair; }
	public function setPair($pair) { $this->pair = $pair; return $this; }

	public function getPriceSingle() { return $this->priceSingle; }
	public function setPriceSingle($priceSingle) { $this->priceSingle = $priceSingle; return $this; }

	public function getPricePair() { return $this->pricePair; }
	public function setPricePair($pricePair) { $this->pricePair = $pricePair; return $this; }
	
	public function getLessons() { return $this->lessons; }
	public function setLessons($lessons) { $this->lessons = $lessons; return $this; }
	
}