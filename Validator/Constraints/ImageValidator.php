<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImageValidator extends ConstraintValidator {

	private $imagine;

	public function __construct(\Imagine\Image\ImagineInterface $imagine) {
		$this->imagine = $imagine;
	}

	/**
	 * @param mixed $value
	 * @param Image $constraint
	 */
	public function validate($value, Constraint $constraint) {
		if ($value === null) {
			return;
		}

		if ($constraint->propertyPath === null) {
			$storerFile = $value;
		} else {
			$accessor = PropertyAccess::createPropertyAccessor();
			try {
				$storerFile = $accessor->getValue($value, $constraint->propertyPath);
			} catch (\Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException $e) {
				throw $e;
			}
		}

		/** @var \Devture\Bundle\StorerBundle\Entity\FileInterface $storerFile **/

		if (!($storerFile instanceof \Devture\Bundle\StorerBundle\Entity\FileInterface)) {
			throw new \InvalidArgumentException('Expected storer file, but got something else.');
		}

		if (is_array($constraint->allowedExtensions)) {
			//File extension check enabled. Treat the storage key as a filename
			//and see if the extension is valid.
			//For this to work, we expect that the caller had preserved the original file extension.
			$extension = \Devture\Bundle\StorerBundle\Util::getExtensionByFileName($storerFile->getStorerFileKey());

			if ($extension === null) {
				$this->context->buildViolation($constraint->messageUnknownFileExtension)
					->addViolation();
				return;
			}

			if (!in_array($extension, $constraint->allowedExtensions)) {
				$this->context->buildViolation($constraint->messageInvalidFileExtension)
					->setParameters([
						'%currentExtension%' => strtoupper($extension),
						'%allowedExtensions%' => implode(', ', array_map('strtoupper', $constraint->allowedExtensions)),
					])
					->addViolation();
				return;
			}
		}

		/** @var \Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface $storerHandle **/
		$storerHandle = $storerFile->getStorerFileHandle();

		if ($storerHandle->isPersistent() && $constraint->skipContentCheckForPersistent) {
			return;
		}

		if ($storerHandle->isPersistent()) {
			$content = $storerHandle->getFile()->getContent();
		} else {
			$content = $storerHandle->getNonPersistentFileContent();
		}

		try {
			$this->imagine->load($content);
		} catch (\Imagine\Exception\Exception $e) {
			$this->context->buildViolation($constraint->messageInvalidImageFile)
				->addViolation();
		}
	}

}
