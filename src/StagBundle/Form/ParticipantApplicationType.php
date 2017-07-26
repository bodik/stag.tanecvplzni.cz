<?php

namespace StagBundle\Form;

use StagBundle\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipantApplicationType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("courseRef", EntityType::class, [
			"label" => "Kurz",
			"class" => "StagBundle:Course",
			"choice_label" => "name",
			"expanded" => false,
			"multiple" => false
			]);
		$builder->add("gn", TextType::class, ["label" => "Jméno"]);
		$builder->add("sn", TextType::class, ["label" => "Příjmení"]);
		$builder->add("email", TextType::class, ['label' => "Email"]);
		$builder->add("phoneNumber", TextType::class, ["label" => "Telefon", "required" => false,]);
		$builder->add("gender", ChoiceType::class, array(
			"label" => "Pohlaví",
			"choices" => [Participant::ALL_GENDERS["MALE"] => "Muž", Participant::ALL_GENDERS["FEMALE"] => "Žena"],
			"choice_label" => function ($value, $key, $index) { return $value; },
			"expanded" => true,
			));
		$builder->add("paired", ChoiceType::class, [
			"label" => "Typ přihlášky",
			"choices" => ["samostatně" => "single", "v páru" => "pair"],
			"choice_label" => function ($value, $key, $index) { return $key; },
			"expanded" => true,
			"mapped" => false
			]);
		$builder->add("partner", TextType::class, ["label" => "Partner", "required" => false,]);
		$builder->add("reference", TextType::class, ["label" => "Reference", "attr" => ["placeholder" => "facebook, web, doporučení od přítele, jiné ..."], "required" => false,]);
		$builder->add("note", TextType::class, ["label" => "Poznámka k přihlášce", "required" => false,]);

		$builder->add("tosagreed", CheckboxType::class, [
			"label" => "Zaškrnutím políčka souhlasíte se zpracováním osobních údajů dle zákona č. 101/2000 Sb., o ochraně osobních údajů, ve znění pozdějších předpisů a zároveň vyjadřujete souhlas s všeobecnými pravidly Tance v Plzni, z. s.",
			"mapped" => false,
		]);
		
		$builder->add('save', SubmitType::class);
	}
}