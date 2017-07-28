<?php

namespace StagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name')
			->add('description')
			->add('teacher')
			->add('place')
			->add('capacity')
			->add('pair', CheckboxType::class, ['required' => false,])
			->add('priceSingle')
			->add('pricePair')
			->add('save', SubmitType::class);
	}
}