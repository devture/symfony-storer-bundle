<?php
namespace Devture\Bundle\StorerBundle\Entity;

use Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface;

class File implements FileInterface {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var FileHandleInterface|null
	 */
	private $fileHandle;

	/**
	 * @var FileInterface|null
	 */
	private $previousFile;

	public function __construct(string $key) {
		$this->key = $key;
	}

	public function getStorerFileKey(): string {
		return $this->key;
	}

	public function getStorerFileHandle(): ?FileHandleInterface {
		return $this->fileHandle;
	}

	public function setStorerFileHandle(?FileHandleInterface $fileHandle): void {
		$this->fileHandle = $fileHandle;
	}

	public function setPreviousFile(?FileInterface $previousFile): void {
		$this->previousFile = $previousFile;
	}

	public function getPreviousFile(): ?FileInterface {
		return $this->previousFile;
	}

}
