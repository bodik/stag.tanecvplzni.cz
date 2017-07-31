<?php

namespace GuserBundle\Form;

use GuserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LostPasswordType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('email', TextType::class, ["required" => false]);
		$builder->add('Reset', SubmitType::class);
	}
}
