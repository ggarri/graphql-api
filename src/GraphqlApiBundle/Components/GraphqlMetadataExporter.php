<?php
/**
 * Created by ggarrido at 7/08/16 22:04.
 * Copyright: 2016, Base7booking
 */

namespace GraphqlApiBundle\Components;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\Export\Driver\AnnotationExporter;
use Doctrine\ORM\Tools\EntityGenerator;


class GraphqlMetadataExporter extends AnnotationExporter
{
	/**
	 * @var EntityGenerator|null
	 */
	private $_entityGenerator;

	/**
	 * @var string
	 */
	private $namespace;


	/**
	 * @param string|null $dir
	 */
	public function __construct($namespace, $dir = null)
	{
		parent::__construct($dir);
		$this->namespace = $namespace;
	}

	public function setEntityGenerator(EntityGenerator $entityGenerator) {
		$this->_entityGenerator = new GraphqlEntityGenerator();
		$this->_entityGenerator->setDefaultNamespace($this->namespace);
		parent::setEntityGenerator($this->_entityGenerator);
	}

	protected function _generateOutputPath(ClassMetadataInfo $metadata)
	{
		return $this->_outputDir . '/' . str_replace('\\', '/', $this->_entityGenerator->getFilename($metadata)) . $this->_extension;
	}
}