<?php
namespace Devture\Bundle\StorerBundle\Listener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Devture\Bundle\StorerBundle\Entity\StorerFilesContainerInterface;

class EventSubscriber implements \Doctrine\Common\EventSubscriber {

	private $storer;

	public function __construct(\Devture\Bundle\StorerBundle\Helper\Storer $storer) {
		$this->storer = $storer;
	}

	public function getSubscribedEvents() {
		return ['prePersist', 'preUpdate', 'postLoad', 'preRemove'];
	}

	public function prePersist(LifecycleEventArgs $event) {
		$document = $event->getEntity();
		if ($document instanceof StorerFilesContainerInterface) {
			$this->persistFilesForContainer($document, $event->getEntityManager());
		}
	}

	public function preUpdate(LifecycleEventArgs $event) {
		$document = $event->getEntity();

		if ($document instanceof StorerFilesContainerInterface) {
			$this->persistFilesForContainer($document, $event->getEntityManager());
		}
	}

	public function postLoad(LifecycleEventArgs $event) {
		$document = $event->getEntity();
		if ($document instanceof StorerFilesContainerInterface) {
			$this->loadFilesForContainer($document, $event->getEntityManager());
		}
	}

	public function preRemove(LifecycleEventArgs $event) {
		$document = $event->getEntity();
		if ($document instanceof StorerFilesContainerInterface) {
			$this->deleteFilesForContainer($document, $event->getEntityManager());
		}
	}

	private function persistFilesForContainer(StorerFilesContainerInterface $container, EntityManager $em): void {
		foreach ($container->getContainedStorerFiles() as $file) {
			$this->storer->persist($file);
		}
	}

	private function loadFilesForContainer(StorerFilesContainerInterface $container, EntityManager $em): void {
		foreach ($container->getContainedStorerFiles() as $file) {
			$this->storer->hydrate($file);
		}
	}

	private function deleteFilesForContainer(StorerFilesContainerInterface $container, EntityManager $em): void {
		foreach ($container->getContainedStorerFiles() as $file) {
			$this->storer->delete($file);
		}
	}

}
