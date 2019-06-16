<?php
namespace Devture\Bundle\StorerBundle\FileHandle;

class VolatileInMemoryDataHandle implements FileHandleInterface {

	/**
	 * @var string
	 */
	private $data;

	public function __construct(string $data) {
		$this->data = $data;
	}

	public function isPersistent(): bool {
		return false;
	}

	public function getNonPersistentFileContent(): string {
		return $this->data;
	}

	public function getFile(): \Gaufrette\File {
		throw new \LogicException('Volatile files are not part of a known persistent filesystem.');
	}

	public function copyTo(FileHandleInterface $destinationHandle): void {
		$destinationFile = $destinationHandle->getFile();
		$destinationFile->setContent($this->data);
	}

	public function getSize(): int {
		return strlen($this->data);
	}

}
