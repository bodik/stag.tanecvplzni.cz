<?php

namespace StagBundle\Form;

use StagBundle\Entity\Participant;
use StagBundle\Repository\TicketRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipantApplicationType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("gn", TextType::class, ["label" => "Jméno"]);

		$builder->add("sn", TextType::class, ["label" => "Příjmení"]);

		$builder->add("email", TextType::class, ['label' => "Email"]);

		$builder->add("phoneNumber", TextType::class, [
			"label" => "Telefon",
			"required" => false,
			]);

		$builder->add("gender", ChoiceType::class, array(
			"label" => "Pohlaví",
			"choices" => Participant::ALL_GENDERS,
			"choice_label" => function ($value, $key, $index) { return $key; },
			"expanded" => true,
			));

		$builder->add("partner", TextType::class, [
			"label" => "Partner",
			"attr" => ["placeholder" => "Uveďte celé jméno partnera (partner musí také vyplnit přihlašovací formulář)"],
			"required" => false,
			]);

		$builder->add("reference", TextType::class, [
			"label" => "Reference",
			"attr" => ["placeholder" => "facebook, web, doporučení od přítele, jiné ..."],
			"required" => false,
			]);

		$builder->add("note", TextType::class, [
			"label" => "Poznámka k přihlášce",
			"required" => false,
			]);

		$builder->add("tosagreed", CheckboxType::class, [
			"label" => "Zaškrnutím políčka souhlasíte se zpracováním osobních údajů dle zákona č. 110/2019 Sb., o ochraně osobních údajů, ve znění pozdějších předpisů a zároveň vyjadřujete souhlas s všeobecnými pravidly Tance v Plzni, z. s.",
			"mapped" => false,
		]);
		
		$builder->add('save', SubmitType::class, ["label" => "Odeslat přihlášku"]);
	}
}
