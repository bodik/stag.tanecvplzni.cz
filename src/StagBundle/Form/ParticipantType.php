<?php

namespace StagBundle\Form;

use StagBundle\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipantType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('ticketRef', EntityType::class, [
			"label" => "Vstup",
			"class" => "StagBundle:Ticket",
    			'choice_label' => function ($ticket) { return $ticket->getCourseRef()->getName() ." - ".$ticket->getName(); },
			"expanded" => false,
			"multiple" => false
		]);

		$builder->add("gn", TextType::class, ["label" => "Jméno"]);
		$builder->add("sn", TextType::class, ["label" => "Přijmení"]);
		$builder->add("email", TextType::class,["label" => "Email"]);
		$builder->add("phoneNumber", TextType::class,["label" => "Telefon"]);
		$builder->add('gender', ChoiceType::class, [ 
			"label" => "Telefon",
			"choices" => Participant::ALL_GENDERS,
			"choice_label" => function ($value, $key, $index) { return $key; },
			"expanded" => true,
		]);
		$builder->add("partner", TextType::class, ["label" => "Partner", "required" => false,]);
		$builder->add("reference", TextType::class, ["label" => "Reference", "required" => false,]);
		$builder->add("note", TextType::class, ["label" => "Poznámka", "required" => false,]);

		$builder->add("deposit", TextType::class, ["label" => "Záloha", "required" => false,]);
		$builder->add("payment", TextType::class, ["label" => "Platba", "required" => false,]);
		$builder->add("paymentReferenceNumber", TextType::class, ["label" => "Vs platby", "required" => false,]);

		$builder->add("save", SubmitType::class, ["label" => "Uložit"]);
	}
}
