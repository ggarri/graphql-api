<?php

namespace GraphqlApiBundle;

use GraphqlApiBundle\DependencyInjection\GraphqlApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraphqlApiBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new GraphqlApiExtension();
	}
}
