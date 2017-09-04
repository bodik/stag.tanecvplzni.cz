<?php

namespace StagBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

class CourseScheduleType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("length", NumberType::class, [
			"data" => 60,
			"constraints" => new Range(["min" => 0]),
			]);
		$builder->add("schedule",TextareaType::class, [ "attr" => ["rows" => 15]] );
		$builder->add('save', SubmitType::class);
	}
}