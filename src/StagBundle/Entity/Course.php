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
	const ALL_TYPES = [
		"pravidelný" => "regular",
		"workshop" => "workshop",
		"párty" => "party"
	];
	
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="name", type="string", length=1024)
	 */
	private $name;

	/**
	 * @ORM\Column(name="type", type="string", length=255)
	 */
	private $type;

	/**
	 * @ORM\Column(name="level", type="string", length=1024, nullable=true)
	 */
	private $level;

	/**
	 * @ORM\Column(name="description", type="text", nullable=true)
	 */
	private $description;
	
	/**
	 * @ORM\Column(name="lecturer", type="string", length=1024, nullable=true)
	 */
	private $lecturer;

	/**
	 * @ORM\Column(name="place", type="string", length=255)
	 */
	private $place;

	/**
	 * @ORM\Column(name="color", type="string", length=255)
	 */
	private $color;

	/**
	 * @ORM\Column(name="payment_info", type="string", length=1024, nullable=true)
	 */
	private $paymentInfo;
	
	/**
	 * @ORM\Column(name="appl_email_text", type="text")
	 */
	private $applEmailText;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Blob")
	 */
	private $pictureRef;
	
	/**
	 * @ORM\Column(name="fbEventUrl", type="string", length=1024, nullable=true)
	 */
	private $fbEventUrl;
	/**
	 * @ORM\Column(name="fbGroupUrl", type="string", length=1024, nullable=true)
	 */
	private $fbGroupUrl;

	/**
	 * @ORM\Column(name="active", type="boolean")
	 */
	private $active;

	/**
	 * @ORM\OneToMany(targetEntity="Lesson", mappedBy="courseRef")
	 */
	private $lessons;
	
	/**
	 * @ORM\OneToMany(targetEntity="Ticket", mappedBy="courseRef")
	 */
	private $tickets;


	public function __construct() {
		$this->type = "regular";
		$this->active = true;
		$this->color = "#cccccc";
		$this->applEmailText = "Vaše přihláška byla prijata\nstag.tanecvplzni.cz";
		$this->lessons = new ArrayCollection();
		$this->tickets = new ArrayCollection();
	}

	public function getId() { return $this->id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; return $this; }
	
	public function getType() { return $this->type; }
	public function setType($type) { $this->type = $type; return $this; }

	public function getLevel() { return $this->level; }
	public function setLevel($level) { $this->level = $level; return $this; }

	public function getDescription() { return $this->description; }
	public function setDescription($description) { $this->description = $description; return $this; }

	public function getLecturer() { return $this->lecturer; }
	public function setLecturer($lecturer) { $this->lecturer = $lecturer; return $this; }

	public function getPlace() { return $this->place; }
	public function setPlace($place) { $this->place = $place; return $this; }

	public function getColor() { return $this->color; }
	public function setColor($color) { $this->color = $color; return $this; }

	public function getPaymentInfo() { return $this->paymentInfo; }
	public function setPaymentInfo($paymentInfo) { $this->paymentInfo = $paymentInfo; return $this; }

	public function getApplEmailText() { return $this->applEmailText; }
	public function setApplEmailText($applEmailText) { $this->applEmailText = $applEmailText; return $this; }

	public function getPictureRef() { return $this->pictureRef; }
	public function setPictureRef($pictureRef) { $this->pictureRef = $pictureRef; return $this; }

	public function getFbEventUrl() { return $this->fbEventUrl; }
	public function setFbEventUrl($fbEventUrl) { $this->fbEventUrl = $fbEventUrl; return $this; }
	
	public function getFbGroupUrl() { return $this->fbGroupUrl; }
	public function setFbGroupUrl($fbGroupUrl) { $this->fbGroupUrl = $fbGroupUrl; return $this; }

	public function getActive() { return (bool) $this->active; }
	public function setActive($active) { $this->active = $active; return $this; }

	public function getLessons() { return $this->lessons; }
	public function setLessons($lessons) { $this->lessons = $lessons; return $this; }
	
	public function getTickets() { return $this->tickets; }
	public function setTickets($tickets) { $this->tickets = $tickets; return $this; }
	
}
