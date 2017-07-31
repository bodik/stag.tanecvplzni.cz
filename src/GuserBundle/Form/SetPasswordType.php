<?php

namespace GuserBundle\Form;

use GuserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SetPasswordType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("new_password1", PasswordType::class);
		$builder->add("new_password2", PasswordType::class);
		$builder->add("Set password", SubmitType::class);
	}
}
