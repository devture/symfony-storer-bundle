<?php
namespace Devture\Bundle\StorerBundle\FileHandle;

class VolatileLocalFileHandle implements FileHandleInterface {

	private $path;

	public function __construct($path) {
		$this->path = $path;
	}

	public function isPersistent(): bool {
		return false;
	}

	public function getNonPersistentFileContent(): string {
		$content = file_get_contents($this->path);
		if ($content === false) {
			throw new \RuntimeException(sprintf('Failed reading file from: %s', $this->path));
		}
		return $content;
	}

	public function getFile(): \Gaufrette\File {
		throw new \LogicException('Volatile files are not part of a known persistent filesystem.');
	}

	public function copyTo(FileHandleInterface $destinationHandle): void {
		$sourceFp = @fopen($this->path, 'rb');

		if ($sourceFp === false) {
			throw new \RuntimeException(sprintf('Cannot open %s for reading', $this->path));
		}

		$destinationFile = $destinationHandle->getFile();

		//Write *something* first, so that the file would be created.
		//Otherwise we can't safely use the streams.
		//Local streams for example cannot create the full file path (parent directories).
		$destinationFile->setContent('');

		/** @var \Gaufrette\Stream $destinationStream **/
		$destinationStream = $destinationFile->createStream();

		$destinationStream->open(new \Gaufrette\StreamMode('wb'));

		while (!feof($sourceFp)) {
			$data = fread($sourceFp, 256000);
			if ($data === false) {
				throw new \RuntimeException(sprintf('Failed reading from source file: %s', $this->path));
			}

			$destinationStream->write($data);
		}

		fclose($sourceFp);
		$destinationStream->flush();
		$destinationStream->close();
	}

	public function getSize(): int {
		$size = filesize($this->path);
		if ($size === false) {
			throw new \RuntimeException(sprintf('Cannot determine size of file: %s', $this->path));
		}
		return $size;
	}

}
