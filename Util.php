<?php
namespace Devture\Bundle\StorerBundle;

class Util {

	public static function getExtensionByFileName(string $fileName): ?string {
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

	public static function getContentTypeByFileName(string $fileName): ?string {
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

	public static function generateFullStorageKey(string $storagePrefix, string $originalName): string {
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

	public static function getThumbnailExtensionByFileName(string $fileName): ?string {
		$extension = self::getExtensionByFileName($fileName);
		if ($extension === 'png') {
			return 'png';
		}

		return 'jpg';
	}

}
