<?php

namespace GraphqlApiBundle\Component;

use GraphqlApiBundle\Service\GraphqlSchema;
use Doctrine\ORM\EntityManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;


class GraphqlObject
{
	/** @var EntityManager  */
	protected $em;

	/** @var GraphqlObjectMeta */
	private $objectMetaData;

	/** @var ObjectType[] */
	protected static $entities = [];


	/**
	 * GraphqlObject constructor.
	 */
	public function __construct()
	{
		$this->objectMetaData = new GraphqlObjectMeta($this);
	}


	/**
	 * @param EntityManager $em
	 */
	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}


	/**
	 * @return String
	 */
	public function getName() {
		return $this->objectMetaData->className;
	}


	/**
	 * @return GraphqlObjectMeta
	 */
	public function getObjectMeta() {
		return $this->objectMetaData;
	}


	/**
	 * @param bool $isPlural
	 * @return ObjectType
	 */
	public function getGraphqlEntity($isPlural = false) {
		$name = $this->getClassName($isPlural);
		// It initializes each object only once
		if(array_key_exists($name, static::$entities)) {
			return static::$entities[$name];
		}

		$fields = array();
		foreach($this->objectMetaData->getInfoProperties() as $propertyId => $propertyInfo) {
			$def = $propertyInfo->getGraphqlField();
			if($propertyInfo->refTargetEntity) {
				$def['resolve'] = function ($root, $args) use ($propertyId, $propertyInfo) {
					# Obtain related object
					/** @var GraphqlObject $obj */
					$obj = GraphqlSchema::$entities[ucfirst($propertyInfo->refTargetEntity)];
					$uniqueAttr = array_keys($obj->getObjectMeta()->getUniqueProperties());
					$repo = $obj->getRepository();

					# Obtain values from related object and append them into args
					if($root && isset($root[$propertyId]) && !empty($root[$propertyId])) {
						foreach($uniqueAttr as $uniCol) {
							if(isset($root[$propertyId][$uniCol]) && $root[$propertyId][$uniCol]){
								$args[$uniCol] = $root[$propertyId][$uniCol];
							}
						}
					} else {
						# It doesn't exists any object related
						return null;
					}

					if(array_intersect($uniqueAttr, array_keys($args))) {
						$result = $repo->findOneBy($args);
					} else {
						$orderBy = (array_key_exists('order', $args)) ? [$args['order'] => 'ASC'] : [];
						$limit = (array_key_exists('limit', $args)) ? $args['limit'] : null;
						unset($args['limit']);
						unset($args['order']);
						$result = $repo->findBy($args, $orderBy, $limit);
					}

					$resultArray = json_decode(json_encode($result), true);
					return $resultArray;
				};
			}

			$fields[$propertyId] = $def;
		}

		static::$entities[$name] = new ObjectType([
			'name' => $name,
			'description' => $this->objectMetaData->classDesc,
			'fields' => $fields
		]);

		return static::$entities[$name];
	}


	/**
	 * @param bool $isPlural
	 * @return array
	 */
	public function getGraphqlQuery($isPlural = false) {
		return [
			'type' => $isPlural ? Type::listOf($this->getGraphqlEntity(true)): $this->getGraphqlEntity(),
			'args' => $this->objectMetaData->getQueryArgs($isPlural),
			'resolve' => function ($root, $args) {
				$uniqueAttr = array_keys($this->objectMetaData->getUniqueProperties());
				$repo = $this->getRepository();
				if(array_intersect($uniqueAttr, array_keys($args))) {
					$result = $repo->findOneBy($args);
				} else {
					$orderBy = (array_key_exists('order', $args)) ? [$args['order'] => 'ASC'] : [];
					$limit = (array_key_exists('limit', $args)) ? $args['limit'] : null;
					unset($args['limit']);
					unset($args['order']);
					$result = $repo->findBy($args, $orderBy, $limit);
				}

				$resultArray = json_decode(json_encode($result), true);
				return $resultArray;
			}
		];
	}


	/**
	 * @param bool $isPlural
	 * @return string
	 */
	public function getQueryName($isPlural = false) {
		return $isPlural
			? strtolower($this->objectMetaData->getClassNamePlural())
			: strtolower($this->objectMetaData->className);
	}


	/**
	 * @param bool $isPlural
	 * @return string
	 */
	public function getClassName($isPlural = false) {
		return $isPlural
			? $this->objectMetaData->getClassNamePlural()
			: $this->objectMetaData->className;
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getRepository() {
		return $this->em->getRepository($this->objectMetaData->classNamespace);
	}
}