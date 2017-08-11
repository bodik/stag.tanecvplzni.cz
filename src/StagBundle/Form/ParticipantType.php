<?php

namespace StagBundle\Form;

use StagBundle\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipantType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('courseRef', EntityType::class, array(
			'class' => 'StagBundle:Course',
			'choice_label' => 'name',
			'expanded' => false,
			'multiple' => false
			));
		$builder->add('gn');
		$builder->add('sn');
		$builder->add('email');
		$builder->add('phoneNumber');
		$builder->add('gender', ChoiceType::class, array(
			'choices' => Participant::ALL_GENDERS,
			'choice_label' => function ($value, $key, $index) { return $key; },
			'expanded' => true,
			));
		$builder->add('paired', ChoiceType::class, array(
			'choices' => Participant::ALL_PAIRS,
			'choice_label' => function ($value, $key, $index) { return $key; },
			'expanded' => true,
			));
		$builder->add('partner', TextType::class, ["required" => false,]);
		$builder->add('reference', TextType::class, ["required" => false,]);
		$builder->add('note', TextType::class, ["required" => false,]);

		$builder->add('deposit', TextType::class, ["required" => false,]);
		$builder->add('payment', TextType::class, ["required" => false,]);

		$builder->add('save', SubmitType::class);
	}
}