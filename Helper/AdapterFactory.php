<?php
namespace Devture\Bundle\StorerBundle\Helper;

class AdapterFactory {

	static public function create(string $storerUri): \Gaufrette\Adapter {
		$config = parse_url($storerUri);

		if (!is_array($config) || !array_key_exists('scheme', $config)) {
			throw new \InvalidArgumentException('Invalid adapter URI: ' . $storerUri);
		}

		if ($config['scheme'] === 'file') {
			return new \Gaufrette\Adapter\Local($config['path']);
		}

		if ($config['scheme'] === 's3') {
			// S3 URI format: s3://<access key>:<secret>@<region>.<host>:<port>/<bucket>
			return self::createS3Adapter($config);
		}

		if ($config['scheme'] === 'azure-blob') {
			// Azure Blob URI format: azure-blob://<account-name>:<account-secret>@<endpoint-host>/<container>
			return self::createAzureBlobAdapter($config);
		}

		throw new \InvalidArgumentException('Not knowing how to create an adapter out of: ' . $storerUri);
	}

	static private function createS3Adapter(array $config): \Gaufrette\Adapter\AwsS3 {
		list($region, $actualHost) = explode('.', $config['host'], 2);

		//Defaults for running locally (e.g. Minio)
		$endpointScheme = 'http';
		$usePathStyleEndpoint = true;

		if ($actualHost === 's3.amazonaws.com') {
			$endpointScheme = 'https';
			$usePathStyleEndpoint = false;
		}

		$endpointUrl = $endpointScheme . '://' . $actualHost;
		if (array_key_exists('port', $config)) {
			$endpointUrl .= ':' . $config['port'];
		}

		$bucketName = ltrim($config['path'], '/');

		$s3Config = [
			'credentials' => [
				'key' => urldecode($config['user']),
				'secret' => urldecode($config['pass']),
			],
			'version' => 'latest',
			'region'  => $region,
			'endpoint' => $endpointUrl,
			'use_path_style_endpoint' => $usePathStyleEndpoint,
		];

		$s3Client = new \Aws\S3\S3Client($s3Config);

		return new \Gaufrette\Adapter\AwsS3($s3Client, $bucketName);
	}

	static private function createAzureBlobAdapter(array $config): \Gaufrette\Adapter\AzureBlobStorage {
		$connectionStringParts = [
			sprintf('BlobEndpoint=https://%s', $config['host']),
			sprintf('AccountName=%s', urldecode($config['user'])),
			sprintf('AccountKey=%s', urldecode($config['pass'])),
		];
		$connectionString = implode(';', $connectionStringParts);

		$containerName = ltrim($config['path'], '/');

		$factory = new \Gaufrette\Adapter\AzureBlobStorage\BlobProxyFactory($connectionString);

		return new \Gaufrette\Adapter\AzureBlobStorage($factory, $containerName);
	}

}
