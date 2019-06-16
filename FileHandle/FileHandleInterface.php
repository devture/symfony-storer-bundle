<?php
namespace Devture\Bundle\StorerBundle\FileHandle;

interface FileHandleInterface {

	/**
	 * Tells whether the file handle is for a file with persistent backing.
	 */
	public function isPersistent(): bool;

	/**
	 * Returns the file content for non-persistent files
	 *
	 * @throws \RuntimeException - when reading the file content fails
	 * @throws \LogicException - when caled on persistent files
	 */
	public function getNonPersistentFileContent(): string;

	/**
	 * Returns the File object that this handle holds
	 * (for persistent files only!)
	 *
	 * @throws \LogicException - when called on non-persistent files
	 */
	public function getFile(): \Gaufrette\File;

	/**
	 * Copies the file represented by this handle to another handle.
	 *
	 * @throws \RuntimeException - when copying fails
	 */
	public function copyTo(FileHandleInterface $destinationHandle): void;

	public function getSize(): int;

}
