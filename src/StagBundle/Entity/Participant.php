<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Course
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity(repositoryClass="StagBundle\Repository\ParticipantRepository")
 */
class Participant {
	const ALL_GENDERS = ["MALE" => 0, "FEMALE" => 1];	
	
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
	 * @ORM\Column(name="email", type="string", length=255)
	 */
	private $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
	 */
	private $phoneNumber;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="gender", type="integer")
	 */
	private $gender;
	
	/**
	 * @var string
	 *
	 * @ORM\Column(name="partner", type="string", length=255, nullable=true)
	 */
	private $partner;

	/**
	 * @var bool
	 *
	 * @ORM\Column(name="paid", type="boolean")
	 */
	private $paid;
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="note", type="string", length=255, nullable=true)
	 */
	private $note;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Course")
	 * @ORM\JoinColumn(name="course_id", referencedColumnName="id", nullable=false)
	 */
	private $courseRef;


	public function __construct() {
		$this->paid = false;
	}

	public function getId() { return $this->id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; return $this; }
	
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; return $this; }
	
	public function getPhoneNumber() { return $this->phoneNumber; }
	public function setPhoneNumber($phoneNumber) { $this->phoneNumber = $phoneNumber; return $this; }
	
	public function getGender() { return $this->gender; }
	public function setGender($gender) { $this->gender = $gender; return $this; }

	public function getPartner() { return $this->partner; }
	public function setPartner($partner) { $this->partner = $partner; return $this; }

	public function getPaid() { return (bool) $this->paid; }
	public function setPaid($paid) { $this->paid = (bool) $paid; return $this; }
	
	public function getNote() { return $this->note; }
	public function setNote($note) { $this->note = $note; return $this; }
	
	public function getCourseRef() { return $this->courseRef; }
	public function setCourseRef($courseRef) { $this->courseRef = $courseRef; return $this; }
	
}