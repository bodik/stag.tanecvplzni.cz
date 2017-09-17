<?php

namespace StagBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TicketType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add("courseRef", EntityType::class, [
			"label" => "Kurz",
			"class" => "StagBundle:Course",
			"choice_label" => "name",
			"expanded" => false,
			"multiple" => false
		]);
		
		$builder->add("name", TextType::class, ["label" => "Název"]);
		$builder->add("price", IntegerType::class, ["label" => "Cena"]);
		$builder->add("active", CheckboxType::class, ["label" => "Aktivní", "required" => false]);

		$builder->add("save", SubmitType::class, ["label" => "Uložit"]);
	}
}