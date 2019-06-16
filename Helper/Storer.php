<?php
namespace Devture\Bundle\StorerBundle\Helper;

use Devture\Bundle\StorerBundle\Entity\FileInterface;
use Devture\Bundle\StorerBundle\Entity\NullFile;
use Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface;
use Devture\Bundle\StorerBundle\FileHandle\PersistentFileHandle;

class Storer {

	/**
	 * @var \Gaufrette\Filesystem
	 */
	private $filesystem;

	/**
	 * @var \SplObjectStorage
	 */
	private $fileToFileHandleMap;

	public function __construct(\Gaufrette\Filesystem $filesystem) {
		$this->filesystem = $filesystem;
		$this->fileToFileHandleMap = new \SplObjectStorage();
	}

	public function persist(FileInterface $file): void {
		if ($file instanceof NullFile) {
			if ($file->getPreviousFile() !== null) {
				$this->delete($file->getPreviousFile());
			}
			return;
		}

		$fileHandle = $file->getStorerFileHandle();

		if ($fileHandle === null) {
			return;
		}

		if ($fileHandle->isPersistent()) {
			return;
		}

		$filesystemFile = $this->filesystem->get($file->getStorerFileKey(), true);
		$destinationHandle = new PersistentFileHandle($filesystemFile);

		$fileHandle->copyTo($destinationHandle);
		$file->setStorerFileHandle($destinationHandle);

		$this->fileToFileHandleMap->attach($file, $fileHandle);

		if ($file->getPreviousFile() !== null) {
			$this->delete($file->getPreviousFile());
		}
	}

	public function update(FileInterface $file): void {
		$currentFileHandle = $file->getStorerFileHandle();

		try {
			$originalFileHandle = $this->fileToFileHandleMap[$file];

			if ($currentFileHandle === $originalFileHandle) {
				// Same handle. No changes at all.
				return;
			}

			// The old handle was replaced by a new one. Delete it.
			if ($originalFileHandle instanceof FileHandleInterface) {
				$originalFileHandle->getFile()->delete();
			}
		} catch (\UnexpectedValueException $e) {
		}

		// No previous handle. Just save the new one.
		$this->persist($file);
	}

	public function delete(FileInterface $file): void {
		try {
			$this->filesystem->delete($file->getStorerFileKey());
		} catch (\Gaufrette\Exception\FileNotFound $e) {
		}

		$file->setStorerFileHandle(null);
		$this->fileToFileHandleMap->detach($file);
	}

	public function hydrate(FileInterface $file): void {
		$fileHandle = new PersistentFileHandle(function () use ($file) {
			return $this->filesystem->get($file->getStorerFileKey());
		});

		$this->fileToFileHandleMap->attach($file, $fileHandle);

		$file->setStorerFileHandle($fileHandle);
	}

	/**
	 * @return FileInterface[]
	 */
	public function getFilesByPrefix(string $prefix): array {
		$result = $this->filesystem->listKeys($prefix);

		return array_map(function (string $key): FileInterface {
			return new \Devture\Bundle\StorerBundle\Entity\File($key);
		}, $result['keys']);
	}

	public function deleteFilesByPrefix(string $prefix): void {
		foreach ($this->getFilesByPrefix($prefix) as $file) {
			$this->delete($file);
		}
	}

}
