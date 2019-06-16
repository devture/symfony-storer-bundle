# Description

A Symfony bundle that lets you deal with uploaded files in a relatively sane and storage-provider-independent way.

The [Gaufrette](https://github.com/KnpLabs/Gaufrette) library is used for storage abstraction.

Currently, the following storage adapters can be used through the bundle:
- the local filesystem
- Amazon S3 or Minio
- Azure Blob storage

With minor code changes, all other Gaufrette-supported adapters (see `Helper/AdapterFactory.php`) could be made to work.
Pull requests are welcome!


# Installation

Install through composer (`composer require --dev devture/symfony-web-command-bundle`).

Add to `config/bundles.php`:

```php
Devture\Bundle\StorerBundle\DevtureStorerBundle::class => ['all' => true],
```

Depending on the storage provider that you'd like to use, you may need to install additional libraries (e.g. `aws/aws-sdk-php`, etc.)


## Configuration

Drop the following config in `config/packages/devture_storer.yaml`

```yaml
devture_storer:
  adapter_uri: '%env(resolve:DEVTURE_STORER_ADAPTER_URL)%'
  validation_max_size_megabytes: 20
```

You then need to define an environment variable `DEVTURE_STORER_ADAPTER_URL`, which would specify which storage adapter you'd like to use.

Example (`.env`):

```
# Local and remote adapters are supported.
#
# Remote URLs usually require that their secrets be urlencoded and percentage-sign-escaped (`/` turned to `%%2F`).
# Reason: `/` found in secrets breaks URL-parsing, so we urlencode it. Symfony thinks % is a parameter, so we escape it (as %%).
#
# Example of S3-compatible Minio URL:
#	DEVTURE_STORER_ADAPTER_URL=s3://access.key:key%%2Fwith%%2Furlencoded%%2Fslashes@us-east-1.localhost:9000/bucket.name
#
# Example of actual Amazon S3 URL:
#	DEVTURE_STORER_ADAPTER_URL=s3://access.key:key%%2Fwith%%2Furlencoded%%2Fslashes@ap-northeast-1.s3.amazonaws.com/bucket.name
#
# Example of actual Azure Blob URL:
#	DEVTURE_STORER_ADAPTER_URL=azure-blob://account-name:account-key%%2Fwith%%2Furlencoded%%2Fslashes@account.blob.core.windows.net/container
DEVTURE_STORER_ADAPTER_URL=file://%kernel.project_dir%/var/storer
```

You also need to register a Doctrine type like this (in `config/doctrine.yaml`):

```yaml
doctrine:
    dbal:
        # Other stuff here ...
        types:
            devture_storer.file: Devture\Bundle\StorerBundle\Doctrine\StorerFileType
```


# Usage example

## Entity

```php
/**
 * @ORM\Table(name="user")
 */
class User implements \Devture\Bundle\StorerBundle\Entity\StorerFilesContainerInterface {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="devture_storer.file", length=191, nullable=true)
	 * @Devture\Bundle\StorerBundle\Validator\Constraints\Image()
	 * @Devture\Bundle\StorerBundle\Validator\Constraints\MaximumSize(maxSizeMegabytes=10)
	 */
	private $photo;

	public function getPhoto(): ?\Devture\Bundle\StorerBundle\Entity\FileInterface {
		return $this->photo;
	}

	public function setPhoto(?\Devture\Bundle\StorerBundle\Entity\FileInterface $photo) {
		$this->photo = $photo;
	}

	/**
	 * {@inheritDoc}
	 * @see \Devture\Bundle\StorerBundle\Entity\StorerFilesContainerInterface::getContainedStorerFiles()
	 */
	public function getContainedStorerFiles(): array {
		$files = [];
		if ($this->photo !== null) {
			$files[] = $this->photo;
		}
		return $files;
	}

}
```

## Form Type

```php
<?php
namespace App\UserBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Devture\Bundle\StorerBundle\FileHandle\VolatileLocalFileHandle;

class UserType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		/** @var \App\UserBundle\Entity\User $entity */
		$entity = $options['data'];

		$builder->add('photoUpload', FileType::class, [
			'label' => 'Photo',
			'mapped' => false,
			'required' => false,
		]);

		$builder->get('photoUpload')->addModelTransformer(new CallbackTransformer(
			function ($whatever): ?\Devture\Bundle\StorerBundle\Entity\FileInterface {
				// No need to transform "to form".
				return $whatever;
			},
			function (?UploadedFile $uploadedFile) use ($entity) {
				if ($uploadedFile === null) {
					// Nothing new. Not binding anything.
					return;
				}

				$file = new \Devture\Bundle\StorerBundle\Entity\File(
					\Devture\Bundle\StorerBundle\Util::generateFullStorageKey(
						'user/photo',
						$uploadedFile->getClientOriginalName()
					)
				);
				$file->setStorerFileHandle(new VolatileLocalFileHandle($uploadedFile->getPathname()));

				if ($entity->getPhoto() !== null) {
					// Save the reference to the old photo, so we can delete it
					// (happens automatically when the new one is persisted).
					$file->setPreviousFile($entity->getPhoto());
				}

				$entity->setPhoto($file);
			}
		));
	}

	public function getBlockPrefix() {
		return 'user';
	}

}
```

## Form theme

```twig
{% block _user_photoUpload_widget %}
	{% set user = form.parent.vars.value %}
	{% set photo = user.photo %}

	{% if photo is not none %}
		{% if photo_url_full is not none %}
			<img src="{{ photo|user_generate_photo_serving_link }}" class="img-fluid img-thumbnail" />
		{% endif %}

		{# Some delete button could go here #}
	{% endif %}

	{{ form_widget(form) }}
{% endblock %}
```

## Serving

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\UserBundle\Repository\UserRepository;

/**
 * @Route("/user/{userId}/photo", requirements={"userId": "\d+"})
 */
class UserPhotoController extends AbstractController {

	/**
	 * @Route("/serve", name="user.photo.serve", methods={"GET"})
	 */
	public function serve(
		Request $request,
		int $userId,
		UserRepository $userRepository
	): Response {
		/** @var User|null $user */
		$user = $userRepository->find($userId);
		if ($user === null || $user->getPhoto() === null) {
			throw $this->createNotFoundException('No photo');
		}

		/** @var \Devture\Bundle\StorerBundle\Entity\FileInterface $photo **/
		$photo = $user->getPhoto();

		return new \Devture\Bundle\StorerBundle\Http\FileResponse($photo->getStorerFileHandle(), 200, [
			'Content-Type' => \Devture\Bundle\StorerBundle\Util::getContentTypeByFileName($photo->getStorerFileKey()),
		]);
	}

}
```

## Deletion

```php
$previousFile = $user->getPhoto();

if ($previousFile !== null) {
	// Setting a new special `NullFile` with a reference to the one we want to delete
	// (called "previous"), will ensure that `Storer` would delete it.
	$user->setPhoto(new \Devture\Bundle\StorerBundle\Entity\NullFile($previousFile));
	$entityManager->flush($user);
}
```
