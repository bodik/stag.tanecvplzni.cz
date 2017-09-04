<?php

namespace StagBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LessonType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('courseRef', EntityType::class, array(
			'class' => 'StagBundle:Course',
			'choice_label' => 'name',
			'expanded' => false,
			'multiple' => false
			));
		
		$builder->add('time', DateTimeType::class, [ 'widget' => 'single_text',]);
		$builder->add('length');
		$builder->add('level', TextType::class, ["required" => false]);
		$builder->add('lecturer', TextType::class, ["required" => false]);
		$builder->add('description', TextType::class, ["required" => false]);

		$builder->add('save', SubmitType::class);
	}
}