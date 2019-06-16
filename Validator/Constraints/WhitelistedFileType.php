<?php
namespace Devture\Bundle\StorerBundle\Validator\Constraints;

/**
 * @Annotation
 */
class WhitelistedFileType extends \Symfony\Component\Validator\Constraint {

	public $messageUnknownFileExtension = 'devture_storer.validation_error.unknown_file_extension';
	public $messageInvalidFileExtension = 'devture_storer.validation_error.invalid_file_extension_non_explanatory';

	public $skipCheckForPersistent = true;

	public $allowedExtensions = [
		// Images
		'jpg', 'jpeg', 'png', 'bmp', 'tiff', 'webp', 'svg', 'gif', 'ico', 'tif',

		// Documents
		'pdf', 'xps',
		'doc', 'dot', 'docx',
		'odt', 'sxw', 'pages', 'ps', 'tex',
		'rtf',

		// Book
		'epub', 'mobi', 'azw3', 'ibooks',

		// Others
		'xmind',

		// fonts
		'ttf', 'otf', 'fon', 'fnt',

		// Graphics
		'psd', 'psb',
		'xcf',
		'ai',
		'cdr',
		'swf', 'eps', 'fla',

		// Drawing
		'dwg',
		'slddrw', 'sldprt',

		// Plain-text
		'txt',
		'log',
		'srt',

		// Configuration
		'ini',

		// Styles
		'css', 'sass', 'scss', 'less',

		// Certificates and keys
		'pem', 'cer', 'ppk', 'ca', 'csr', 'crt',

		// Spreadsheets
		'xls', 'xlt', 'xlsx', 'xltx','xlsm',
		'numbers', 'ods', 'ots',


		// Presentations
		'ppt', 'pps', 'ppsx',
		'odp', 'otp','pptx',

		// Database exports
		'mdb', 'sql',

		// Data-exchange
		'json', 'xml', 'yml', 'yaml', 'csv',

		// Archives
		'zip', 'rar', '7z',
		'tar', 'tar.bz2', 'tb2', 'tbz', 'tbz2',
		'tar.gz', 'tgz', 'gz',
		'tar.lz',
		'tar.lzma', 'tlz',
		'tar.xz', 'tgz',
		'tar.z', 'tz',

		// Encrypted
		'pub','gpg',

		// Video
		'avi', 'mkv', 'mpg', 'mpeg', 'mp4', 'm4v', 'wmv',

		// Audio
		'wma', 'mp3', 'aac', 'ogg', 'flac', 'flac', 'wav',
	];

	public $propertyPath = null;

	public function validatedBy() {
		return WhitelistedFileTypeValidator::class;
	}

}
