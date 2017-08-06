<?php

namespace StagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BlobType extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('fileHandler', FileType::class);
		$builder->add('save', SubmitType::class);
	}
}