<?php

namespace StagBundle\Form;

use StagBundle\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', TextType::class, ["label" => "Jméno"]);

		$builder->add('type', ChoiceType::class, [
			"label" => "Typ",
			"choices" => Course::ALL_TYPES,
			"choice_label" => function ($value, $key, $index) { return $key; },
			"expanded" => true,
			]
		);

		$builder->add('level', TextType::class, ["label" => "Úroveň", "required" => false]);
		$builder->add('description', TextareaType::class, ["label" => "Popis", "required" => false, "attr" => [ "rows" => 10] ]);
		$builder->add('lecturer', TextType::class, ["label" => "Lektor", "required" => false]);
		$builder->add('place', TextType::class, ["label" => "Místo"]);

		$builder->add('color', TextType::class, ["label" => "Barva", "attr" => ["class" => "jscolor {hash:true, uppercase:false}"]]);
		$builder->add('applEmailText', TextareaType::class, ["label" => "Email odpovědi", "attr" => [ "rows" => 10] ]);

		$builder->add('pictureRef', EntityType::class, [
			"label" => "Obrázek",
			"class" => "StagBundle:Blob",
			"choice_label" => "FileName",
			"expanded" => false,
			"multiple" => false
			]);		
		
		$builder->add("save", SubmitType::class, ["label" => "Uložit"]);
	}
}
