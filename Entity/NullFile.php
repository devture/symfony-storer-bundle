<?php
namespace Devture\Bundle\StorerBundle\Entity;

use Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface;

/**
 * NullFile is a special file to present a "delete request".
 * It can be used to easily make `Storer` delete a given previous file.
 *
 * Just put a new `NullFile` reference in your entity and make that refer
 * to the previous file, and `Storer` would take care of it.
 *
 * And because the `StorerFileType` doctrine type has special handling for
 * `NullFile`, the resulting database value (available on the next request) would also be `null`.
 */
class NullFile implements FileInterface {

	/**
	 * @var FileInterface|null
	 */
	private $previousFile;

	public function __construct(?FileInterface $previousFile) {
		$this->previousFile = $previousFile;
	}

	public function getStorerFileKey(): string {
		return '';
	}

	public function getStorerFileHandle(): ?FileHandleInterface {
		return null;
	}

	public function setStorerFileHandle(?FileHandleInterface $fileHandle): void {
		throw new \LogicException('Not supposed to modify a null file');
	}

	public function setPreviousFile(?FileInterface $previousFile): void {
		throw new \LogicException('Not supposed to modify a null file');
	}

	public function getPreviousFile(): ?FileInterface {
		return $this->previousFile;
	}

}
