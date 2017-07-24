<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Course
 *
 * @ORM\Table(name="course")
 * @ORM\Entity(repositoryClass="StagBundle\Repository\CourseRepository")
 */
class Course {
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=65535, nullable=true)
	 */
	private $description;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="teacher", type="string", length=255)
	 */
	private $teacher;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="place", type="string", length=255)
	 */
	private $place;

	/**
	 * @var int
	 *
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
	 * @var int
	 *
	 * @ORM\Column(name="price_single", type="integer")
	 */
	private $priceSingle;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="price_pair", type="integer")
	 */
	private $pricePair;

	/**
	 * @var array
	 *
	 * @ORM\Column(name="lessons", type="array")
	 */
	private $lessons;

	public function __construct() {
		$this->pair = false;
		$this->lessons = [];
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