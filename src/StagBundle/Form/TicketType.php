<?php

namespace StagBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TicketType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('courseRef', EntityType::class, array(
			'class' => 'StagBundle:Course',
			'choice_label' => 'name',
			'expanded' => false,
			'multiple' => false
			));
		
		$builder->add('name');
		$builder->add('price');

		$builder->add('save', SubmitType::class);
	}
}