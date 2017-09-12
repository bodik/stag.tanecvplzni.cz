<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Course
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="StagBundle\Repository\TicketRepository")
 */
class Ticket {
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
	 * @ORM\Column(name="price", type="integer")
	 */
	private $price;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="tickets")
	 * @ORM\JoinColumn(name="course_id", referencedColumnName="id", nullable=false)
	 */
	private $courseRef;
	
	/**
	 * @ORM\OneToMany(targetEntity="Participant", mappedBy="ticketRef")
	 */
	private $participants;
	
	

	public function __construct() {
		$this->participants = new ArrayCollection();
	}

	public function getId() { return $this->id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; return $this; }

	public function getPrice() { return $this->price; }
	public function setPrice($price) { $this->price = $price; return $this; }
	
	public function getCourseRef() { return $this->courseRef; }
	public function setCourseRef($courseRef) { $this->courseRef = $courseRef; return $this; }
	
	public function getParticipants() { return $this->participants; }
	public function setParticipants($participants) { $this->participants = $participants; return $this; }
}