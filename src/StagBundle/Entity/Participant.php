<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Course
 *
 * @ORM\Table(name="participant", options={"auto_increment": 1024})
 * @ORM\Entity(repositoryClass="StagBundle\Repository\ParticipantRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Participant {
	const ALL_GENDERS = ["muž" => "male", "žena" => "female"];
	const ALL_PAIRS = ["samostatně" => "single", "v páru" => "pair"];
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="sn", type="string", length=255)
	 */
	private $sn;

	/**
	 * @ORM\Column(name="gn", type="string", length=255)
	 */
	private $gn;

	/**
	 * @ORM\Column(name="email", type="string", length=255)
	 * @Assert\Email(message = "Hodnota '{{ value }}' není spravně zapsaná adresa.", checkMX=true)
	 */
	private $email;

	/**
	 * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
	 */
	private $phoneNumber;

	/**
	 * @ORM\Column(name="gender", type="string", length=255)
	 */
	private $gender;
	
	/**
	 * @ORM\Column(name="paired", type="string", length=255)
	 */
	private $paired;

	/**
	 * @ORM\Column(name="partner", type="string", length=255, nullable=true)
	 */
	private $partner;
	
	/**
	 * @ORM\Column(name="reference", type="string", length=255, nullable=true)
	 */
	private $reference;	

	/**
	 * @ORM\Column(name="note", type="string", length=255, nullable=true)
	 */
	private $note;

	/**
	 * @ORM\Column(name="deposit", type="string", length=255, nullable=true)
	 */
	private $deposit;

	/**
	 * @ORM\Column(name="payment", type="string", length=255, nullable=true)
	 */
	private $payment;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="participants")
	 * @ORM\JoinColumn(name="course_id", referencedColumnName="id", nullable=false)
	 */
	private $courseRef;
	
	/**
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;
	/**
	 * @ORM\Column(name="modified", type="datetime")
	 */
	private $modified;
	
	


	public function __construct() {
		$this->paid = false;
	}

	public function getId() { return $this->id; }

	public function getSn() { return $this->sn; }
	public function setSn($sn) { $this->sn = $sn; return $this; }

	public function getGn() { return $this->gn; }
	public function setGn($gn) { $this->gn = $gn; return $this; }
	
	public function getEmail() { return $this->email; }
	public function setEmail($email) { $this->email = $email; return $this; }
	
	public function getPhoneNumber() { return $this->phoneNumber; }
	public function setPhoneNumber($phoneNumber) { $this->phoneNumber = $phoneNumber; return $this; }

	public function getGender() { return $this->gender; }
	public function setGender($gender) { $this->gender = $gender; return $this; }

	public function getPaired() { return $this->paired; }
	public function setPaired($paired) { $this->paired = $paired; return $this; }

	public function getPartner() { return $this->partner; }
	public function setPartner($partner) { $this->partner = $partner; return $this; }

	public function getReference() { return $this->reference; }
	public function setReference($reference) { $this->reference = $reference; return $this; }

	public function getNote() { return $this->note; }
	public function setNote($note) { $this->note = $note; return $this; }

	public function getDeposit() { return $this->deposit; }
	public function setDeposit($deposit) { $this->deposit = $deposit; return $this; }

	public function getPayment() { return $this->payment; }
	public function setPayment($payment) { $this->payment = $payment; return $this; }
	
	public function getCourseRef() { return $this->courseRef; }
	public function setCourseRef($courseRef) { $this->courseRef = $courseRef; return $this; }
	
	public function getCreated() { return $this->created; }
	public function getModified() { return $this->modified; }
	
	/* lifecycle hooks */
	
	/** @ORM\PrePersist */
	public function macTimesOnPrePersist() {
		$this->created = new \DateTime();
		$this->modified = $this->created;
    	}
	/** @ORM\PreUpdate */
	public function macTimesOnPreUpdate() {
		$this->modified = new \DateTime();
	}
}