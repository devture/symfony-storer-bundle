<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class WhitelistedFileTypeValidator extends ConstraintValidator {

	/**
	 * @param mixed $value
	 * @param WhitelistedFileType $constraint
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

		/** @var \Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface $storerHandle **/
		$storerHandle = $storerFile->getStorerFileHandle();

		if ($storerHandle->isPersistent() && $constraint->skipCheckForPersistent) {
			return;
		}

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
				])
				->addViolation();
			return;
		}
	}

}
