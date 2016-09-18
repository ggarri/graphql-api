<?php
/**
 * Created by ggarrido at 18/09/16 16:28.
 * Copyright: 2016, Base7booking
 */

namespace GraphqlApiBundle\Routing;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader
{
	public function load($resource, $type = null)
	{
		$collection = new RouteCollection();

		$resource = '@GraphqlApiBundle/Resources/config/routing.yml';
		$importedRoutes = $this->import($resource, 'yaml');
		$collection->addCollection($importedRoutes);
		return $collection;
	}

	public function supports($resource, $type = null)
	{
		return 'extra' === $type;
	}
}