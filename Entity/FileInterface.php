<?php
namespace Devture\Bundle\StorerBundle\Entity;

use Devture\Bundle\StorerBundle\FileHandle;

interface FileInterface {

	public function getStorerFileKey(): string;

	public function setStorerFileHandle(?FileHandle\FileHandleInterface $handle): void;
	public function getStorerFileHandle(): ?FileHandle\FileHandleInterface;

	public function setPreviousFile(?FileInterface $previousFile): void;
	public function getPreviousFile(): ?FileInterface;

}
