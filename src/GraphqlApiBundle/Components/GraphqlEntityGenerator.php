<?php
/**
 * Created by ggarrido at 7/08/16 22:18.
 * Copyright: 2016, Base7booking
 */

namespace GraphqlApiBundle\Components;

use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class GraphqlEntityGenerator extends EntityGenerator
{
	protected $fieldVisibility = 'public';

	protected $defaultNamespace;

//	protected $classToExtend = 'GraphqlObject';

	public function getFilename(ClassMetadataInfo $metadata)
    {
		return $this->getClassName($metadata);
	}

	public function setDefaultNamespace($namespace)
    {
		$this->defaultNamespace = $namespace;
	}

	protected function getClassName(ClassMetadataInfo $metadata)
	{
		$className =  ($pos = strrpos($metadata->name, '\\'))
			? substr($metadata->name, $pos + 1, strlen($metadata->name)) : $metadata->name;

		return ucfirst($this->trimSchema($className));
	}

	protected function generateTableAnnotation($metadata)
	{
		$metadata->table['schema'] = $this->extractSchema($metadata->table['name']);
		$metadata->table['name'] = $this->trimSchema($metadata->table['name']);
		return parent::generateTableAnnotation($metadata);
	}

	protected function generateFieldMappingPropertyDocBlock(array $fieldMapping, ClassMetadataInfo $metadata)
	{
		if ($metadata->sequenceGeneratorDefinition) {
			$metadata->sequenceGeneratorDefinition['sequenceName'] =
				$this->trimSchema($metadata->sequenceGeneratorDefinition['sequenceName']);
		}

		return parent::generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
	}

	protected function generateEntityNamespace(ClassMetadataInfo $metadata)
	{
		if ($this->hasNamespace($metadata)) {
			return 'namespace ' . $this->getNamespace($metadata) .';';
		} else {
			return 'namespace ' . $this->defaultNamespace .';';
		}
	}

	protected function generateAssociationMappingPropertyDocBlock(array $associationMapping, ClassMetadataInfo $metadata)
	{
		$entityName = $this->extractClassNameFromNamespace($associationMapping['targetEntity']);
		$associationMapping['targetEntity'] = $entityName;
		$lines = parent::generateAssociationMappingPropertyDocBlock($associationMapping, $metadata);
		$lines = str_replace("\\$entityName", $entityName, $lines);
		return $lines;
	}

	protected function generateEntityClassName(ClassMetadataInfo $metadata)
	{
		return 'class ' . $this->getClassName($metadata) . ' extends GraphqlObject';
	}

	protected function generateEntityUse()
	{
		return "\n".
		'use Doctrine\ORM\Mapping as ORM;'."\n".
		'use GraphqlApiBundle\Entity\GraphqlObject;'."\n";
	}

	private function trimSchema($className)
    {
		if(($pos = strpos($className, '.')) !== false) {
			$className = substr($className, $pos+1);
		}

		return str_replace('"', '', $className);
	}

	private function extractSchema($className) {
		if(($pos = strpos($className, '.')) !== false) {
			$from = strrchr($className, '\\') === false ? 0 : strpos($className, strrchr($className, '\\'));
			return substr($className, $from, $pos-$from);
		}

		return null;
	}

	private function extractClassNameFromNamespace($namespace)
	{
		$pieces = explode('\\', $namespace);
		$newPieces = '';
		foreach($pieces as $piece) {
			$newPieces[] = ucfirst($this->trimSchema($piece));
		}

		return end($newPieces);
	}
	
	
	private function extractNamespace($namespace)
	{
		$pieces = explode('\\', $namespace);
		$newPieces = '';
		foreach($pieces as $piece) {
			$newPieces[] = ucfirst($this->trimSchema($piece));
		}

		$newNamespace = implode('\\', $newPieces);
		return $newNamespace;
	}
}