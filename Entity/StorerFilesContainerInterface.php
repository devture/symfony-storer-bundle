<?php
namespace Devture\Bundle\StorerBundle\Entity;

interface StorerFilesContainerInterface {

	/**
	 * @return FileInterface[]
	 */
	public function getContainedStorerFiles(): array;

}
