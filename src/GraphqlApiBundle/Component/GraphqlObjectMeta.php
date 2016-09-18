<?php

namespace GraphqlApiBundle\Component;

use Doctrine\ORM\Mapping\Table;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use GraphQL\Type\Definition\Type;


class GraphqlObjectMeta
{
	const ANNOTATION_LABEL = 'Graphql';

	/** @var String  */
	public $classNamespace;

	/** @var String  */
	public $className;

	/** @var String */
	public $classDesc;

	/** @var \ReflectionProperty [] */
	protected $properties;

	/** @var \ReflectionClass */
	protected $class;

	/**
	 * @param GraphqlObject $object
	 */
	public function __construct($object)
	{
		$this->class = new \ReflectionClass($object);
		$this->properties = $this->class->getProperties();;

		$this->classNamespace = $this->class->getName();
		$this->className = $this->class->getShortName();
		$classAnnotations = $this->getClassDoc();
		$this->classDesc = $classAnnotations['desc'];
	}

	/**
	 * @return GraphqlPropertyMeta[]
	 */
	public function getInfoProperties() {
		$items = [];
		foreach($this->properties as $property) {
			if(!$property->isPublic()) continue;
			$propertyMeta = $this->getPropertyMeta($property);
			if(!$propertyMeta) continue;
			$items[$property->getName()] = $propertyMeta;
		}

		return $items;
	}


	public function getQueryArgs($isPlural = false) {
		$args = [];
		foreach($this->getInfoProperties() as $propertyId => $propertyInfo) {
			if(isset($propertyInfo->refTargetEntity)) {
//				$args[$propertyId] = $propertyInfo->getGraphqlQuery();
			} else if(!$isPlural && ($propertyInfo->pk || $propertyInfo->unique)) {
				$args[$propertyId] = $propertyInfo->getGraphqlQuery();
			} else if($isPlural && !$propertyInfo->pk && !$propertyInfo->unique){
				$args[$propertyId] = $propertyInfo->getGraphqlQuery();
			}
		}

		if($isPlural) {
			$args['limit'] = [
				'type' => Type::int()
			];
			$args['order'] = [
				'type' => Type::string()
			];
		}

		return $args;
	}


	public function getUniqueProperties() {
		$properties = [];
		foreach($this->getInfoProperties() as $propertyId => $propertyInfo) {
			if(($propertyInfo->pk || $propertyInfo->unique)) {
				$properties[$propertyId] = $propertyInfo;
			}
		}
		return $properties;
	}

	public function getClassNamePlural() {
		return $this->className . 's';
	}

	protected function getClassDoc()
	{
		$annotationReader = new DoctrineAnnotationReader();
		$classAnnotations = $annotationReader->getClassAnnotations($this->class);
		$info = ['desc' => '@TODO'];
		foreach($classAnnotations as $annotation) {
			if($annotation instanceof Table) {
				$info['name'] = $annotation->name;
			}
		}
		return $info;
	}

	/**
	 * @param $property \ReflectionProperty
	 * @return GraphqlPropertyMeta
	 */
	protected function getPropertyMeta(\ReflectionProperty $property){
		$annotationReader = new DoctrineAnnotationReader();
		$propertyAnnotations = $annotationReader->getPropertyAnnotations($property);
		if(empty($propertyAnnotations)) return null;
		return new GraphqlPropertyMeta($propertyAnnotations);
	}

}