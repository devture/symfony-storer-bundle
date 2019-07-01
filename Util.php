<?php
namespace Devture\Bundle\StorerBundle;

class Util {

	/**
	 * Makes a filename safe for filesystem use.
	 *
	 * Source: https://stackoverflow.com/a/42058764
	 */
	static public function filterFileName(string $fileName, bool $beautify = true): string {
		// sanitize filename
		$fileName = preg_replace(
			'~
			[<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
			[\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
			[\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
			[#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
			[{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
			~x',
			'-',
			$fileName
		);

		// avoids ".", ".." or ".hiddenFiles"
		$fileName = ltrim($fileName, '.-');

		// optional beautification
		if ($beautify) {
			$fileName = static::beautifyFileName($fileName);
		}

		// maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		$fileName = mb_strcut(pathinfo($fileName, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($fileName)) . ($ext ? '.' . $ext : '');
		return $fileName;
	}

	/**
	 * Makes a filename prettier/cleaner.
	 *
	 * Source: https://stackoverflow.com/a/42058764
	 */
	static public function beautifyFileName(string $fileName): string {
		// reduce consecutive characters
		$fileName = preg_replace(
			[
				// "file   name.zip" becomes "file-name.zip"
				'/ +/',
				// "file___name.zip" becomes "file-name.zip"
				'/_+/',
				// "file---name.zip" becomes "file-name.zip"
				'/-+/'
			],
			'-',
			$fileName
		);

		$fileName = preg_replace(
			[
			// "file--.--.-.--name.zip" becomes "file.name.zip"
			'/-*\.-*/',
			// "file...name..zip" becomes "file.name.zip"
			'/\.{2,}/'
			],
			'.',
			$fileName
		);

		// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
		$fileName = mb_strtolower($fileName, mb_detect_encoding($fileName));

		// ".file-name.-" becomes "file-name"
		$fileName = trim($fileName, '.-');

		return $fileName;
	}

	static public function getExtensionByFileName(string $fileName): ?string {
		$parts = explode('.', $fileName);
		if (count($parts) === 1) {
			return null;
		}

		$lastPart = array_pop($parts);
		if ($lastPart === null) {
			// We don't expect this to happen.
			// After all, we had already done a check and know `$parts` has at least 1 element.
			// To keep static-analyzers happy though, we're having this extra check.
			return null;
		}

		$extension = strtolower($lastPart);
		$extension = preg_replace('/([^a-z0-9])/', '', $extension);

		if ($extension === '') {
			return null;
		}

		if (count($parts) > 1) {
			//If there are more parts (besides the actual name),
			//consider the possibility that we're dealing with a "something.tar.gz" kind of
			//situation.
			$lastPart = array_pop($parts);
			if ($lastPart === null) {
				// We don't expect this to happen.
				// After all, we had already done a check and know `$parts` has more than 1 element.
				// To keep static-analyzers happy though, we're having this extra check.
				return null;
			}

			$extensionNext = strtolower($lastPart);
			if ($extensionNext === 'tar') {
				$extension = sprintf('%s.%s', $extensionNext, $extension);
			}
		}

		if ($extension === 'jpeg') {
			//Normalize
			$extension = 'jpg';
		}

		return $extension;
	}

	static public function getContentTypeByFileName(string $fileName): ?string {
		static $mimeTypeRepository;
		if ($mimeTypeRepository === null) {
			$mimeTypeRepository = new \Dflydev\ApacheMimeTypes\PhpRepository();
		}

		$extension = self::getExtensionByFileName($fileName);

		if ($extension === null) {
			return 'application/octet-stream';
		}

		$type = $mimeTypeRepository->findType($extension);

		if ($type === null) {
			return 'application/octet-stream';
		}

		return $type;
	}

	static public function generateFullStorageKey(string $storagePrefix, string $originalName): string {
		$extension = self::getExtensionByFileName($originalName);
		$extensionSuffix = ($extension ? sprintf('.%s', $extension) : '');

		$bareFileName = \Ramsey\Uuid\Uuid::uuid4()->toString();

		return sprintf(
			'%s/%s/%s%s',
			trim($storagePrefix, '/'),
			date('Y/m/d'),
			$bareFileName,
			$extensionSuffix
		);
	}

	static public function getThumbnailExtensionByFileName(string $fileName): ?string {
		$extension = self::getExtensionByFileName($fileName);
		if ($extension === 'png') {
			return 'png';
		}

		return 'jpg';
	}

}
