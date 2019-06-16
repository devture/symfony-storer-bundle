<?php
namespace Devture\Bundle\StorerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder('devture_storer');

		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->scalarNode('adapter_url')
					->defaultValue('file:///dev/null')
				->end()
				->integerNode('validation_max_size_megabytes')
					->defaultValue(10)
				->end()
			->end();

		return $treeBuilder;
	}

}
