<?php

namespace GuserBundle\Form;

use GuserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('username');
		$builder->add('email');
		$builder->add('password', TextType::class, ["data" => "", "required" => false,]);
		$builder->add('active', CheckboxType::class, ["required" => false,]);
		$builder->add('roles', ChoiceType::class, array(
			'choices' => User::ALL_ROLES,
			'choice_label' => function ($value, $key, $index) { return $value; },
			'multiple' => true,
			'expanded' => true,
		));
		$builder->add('save', SubmitType::class);
	}
}
