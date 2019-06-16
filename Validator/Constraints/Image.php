<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

/**
 * @Annotation
 */
class Image extends \Symfony\Component\Validator\Constraint {

	public $messageUnknownFileExtension = 'devture_storer.validation_error.unknown_file_extension';
	public $messageInvalidFileExtension = 'devture_storer.validation_error.invalid_file_extension';
	public $messageInvalidImageFile = 'devture_storer.validation_error.invalid_image_file';

	public $skipContentCheckForPersistent = true;
	public $allowedExtensions = ['jpg', 'jpeg', 'png'];
	public $propertyPath = null;

	public function validatedBy() {
		return ImageValidator::class;
	}

}
