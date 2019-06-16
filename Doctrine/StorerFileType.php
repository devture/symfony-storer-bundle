<?php
namespace Devture\Bundle\StorerBundle\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class StorerFileType extends \Doctrine\DBAL\Types\StringType {

	const STORER_FILE_TYPE = 'devture_storer.file';

	public function getName() {
		return self::STORER_FILE_TYPE;
	}

	public function convertToPHPValue($value, AbstractPlatform $platform) {
		if ($value === null) {
			return null;
		}

		return new \Devture\Bundle\StorerBundle\Entity\File($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform) {
		if (!($value instanceof \Devture\Bundle\StorerBundle\Entity\FileInterface)) {
			return null;
		}

		if ($value->getStorerFileKey() === '') {
			// Special handling for `NullFile` and other similarly-unnamed files
			return null;
		}

		return $value->getStorerFileKey();
	}

	public function requiresSQLCommentHint(AbstractPlatform $platform) {
		return true;
	}

}
