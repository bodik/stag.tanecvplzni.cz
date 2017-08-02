<?php

namespace StagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name');
		$builder->add('description');
		$builder->add('teacher');
		$builder->add('place');
		$builder->add('capacity');
		$builder->add('pair', CheckboxType::class, ['required' => false,]);
		$builder->add('priceSingle');
		$builder->add('pricePair');
		$builder->add('color');
		$builder->add('applEmailText', TextareaType::class, ["attr" => [ "rows" => 10] ]);
		$builder->add('save', SubmitType::class);
	}
}