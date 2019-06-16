<?php
namespace Devture\Bundle\StorerBundle\FileHandle;

class PersistentFileHandle implements FileHandleInterface {

	/**
	 * @var \Gaufrette\File|callable
	 */
	private $file;

	/**
	 * @param \Gaufrette\File|callable $file - the file or callback that lazily-loads the file
	 */
	public function __construct($file) {
		$this->file = $file;
	}

	public function isPersistent(): bool {
		return true;
	}

	public function getNonPersistentFileContent(): string {
		throw new \LogicException('You should not be calling this on persistent files');
	}

	public function getFile(): \Gaufrette\File {
		if (is_callable($this->file)) {
			$this->file = call_user_func($this->file);
		}
		return $this->file;
	}

	public function copyTo(FileHandleInterface $destinationHandle): void {
		throw new \LogicException('Persistent files do not support copying at this time.');
	}

	public function getSize(): int {
		if (is_callable($this->file)) {
			$this->file = call_user_func($this->file);
		}

		return $this->file->getSize();
	}

}
