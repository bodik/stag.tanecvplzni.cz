<?php

namespace StagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TicketActiveButtonType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add("submit", SubmitType::class);
	}
}
