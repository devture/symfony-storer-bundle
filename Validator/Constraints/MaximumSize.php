<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

/**
 * @Annotation
 */
class MaximumSize extends \Symfony\Component\Validator\Constraint {

	public $message = 'devture_storer.validation_error.maximum_size_excedeed';

	public $skipContentCheckForPersistent = true;
	public $propertyPath = null;
	public $maxSizeMegabytes = null;

	public function validatedBy() {
		return MaximumSizeValidator::class;
	}

}
