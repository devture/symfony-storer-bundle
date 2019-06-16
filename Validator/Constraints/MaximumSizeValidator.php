<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MaximumSizeValidator extends ConstraintValidator {

	private $defaultMaxSizeMegabytes;

	public function __construct(int $defaultMaxSizeMegabytes) {
		$this->defaultMaxSizeMegabytes = $defaultMaxSizeMegabytes;
	}

	/**
	 * @param mixed $value
	 * @param MaximumSize $constraint
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

		$maximumSizeMegabytes = ($constraint->maxSizeMegabytes ?: $this->defaultMaxSizeMegabytes);

		/** @var \Devture\Bundle\StorerBundle\Entity\FileInterface $storerFile **/

		if (!($storerFile instanceof \Devture\Bundle\StorerBundle\Entity\FileInterface)) {
			throw new \InvalidArgumentException('Expected storer file, but got something else.');
		}

		/** @var \Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface $storerHandle **/
		$storerHandle = $storerFile->getStorerFileHandle();

		if ($storerHandle->isPersistent() && $constraint->skipContentCheckForPersistent) {
			return;
		}

		$handleSizeMegabytes = round($storerHandle->getSize() / 1024 / 1024, 1);

		if ($handleSizeMegabytes <= $maximumSizeMegabytes) {
			return;
		}

		$this->context->buildViolation($constraint->message)
			->setParameters([
				'%size%' => $handleSizeMegabytes,
				'%max%' => $maximumSizeMegabytes,
			])
			->addViolation();
	}

}
