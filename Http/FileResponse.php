<?php
namespace Devture\Bundle\StorerBundle\Http;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Devture\Bundle\StorerBundle\FileHandle\FileHandleInterface;

class FileResponse extends StreamedResponse {

	public function __construct(FileHandleInterface $handle, int $status = 200, array $headers = array()) {
		parent::__construct(function () use ($handle) {
			/** @var \Gaufrette\File $file */
			$file = $handle->getFile();

			/** @var \Gaufrette\Stream $stream */
			$stream = $file->createStream();

			$stream->open(new \Gaufrette\StreamMode('r'));

			while (!$stream->eof()) {
				echo $stream->read(1 * 1024 * 1024);
			}

			$stream->close();
		}, $status, $headers);
	}

}
