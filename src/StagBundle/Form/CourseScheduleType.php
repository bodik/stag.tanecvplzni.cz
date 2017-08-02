<?php

namespace StagBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseScheduleType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("length", TextType::class, ["data" => 60]);
		$builder->add("note", TextType::class, ["required" => false]);
		$builder->add("schedule",TextareaType::class, [ "attr" => ["rows" => 15]] );
		$builder->add('save', SubmitType::class);
	}
}