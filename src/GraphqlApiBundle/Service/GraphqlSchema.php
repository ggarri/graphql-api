<?php

namespace GraphqlApiBundle\Service;

use GraphqlApiBundle\Entity\GraphqlObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;

/**
 * Class GraphqlSchema
 * @package GraphqlApiBundle\Service
 */
class GraphqlSchema
{

	/** @var GraphqlObject[] */
	public static $entities = [];

	/** @var  EntityManager */
	protected $em;

	/**
	 * @param $entityManager EntityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
	}

	/**
	 *
	 */
	public function loadSchema()
	{
		$meta = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			$entity = $m->getName();
			$this->addEntity(new $entity());
		}
	}


	/**
	 * @param $query
	 * @return array
	 */
	public function execute($query) {
        $schema = $this->getSchema();
		return GraphQL::execute(
			$schema,
			$query,
			/* $rootValue */ null,
			null,
			null
		);
	}

	/**
	 * @param $entityName
	 * @return GraphqlObject|null
	 */
	public function getEntity($entityName) {
		return array_key_exists($entityName, self::$entities) ? self::$entities[$entityName] : null;
	}

	/**
	 * @param string $entityName
	 * @return ObjectType|null
	 * @throws EntityNotFoundException
	 */
	public function getGraphqlEntity($entityName) {
		if(!array_key_exists($entityName, self::$entities)){
			throw new \Exception("Entity $entityName not found");
		}

		return self::$entities[$entityName]->getGraphqlEntity();
	}


	/**
	 * @param $entity GraphqlObject
	 */
	protected function addEntity($entity) {
		$entity->setEntityManager($this->em);
		self::$entities[$entity->getName()] = $entity;
	}


	/**
	 * Return graphql schema
	 * @return Schema
	 */
	protected function getSchema() {
		$fields = [];
		foreach(self::$entities as $entity) {
			$fields[$entity->getQueryName()] = $entity->getGraphqlQuery();
			$fields[$entity->getQueryName(true)] = $entity->getGraphqlQuery(true);
		}

        ksort($fields);

		foreach($fields as &$field) {
			if(!array_key_exists('type', $field)) continue;

			if($field['type'] instanceof ListOfType) {
				$ofType = $field['type']->getWrappedType();
				$this->resolveFields($ofType);
			} else {
				$this->resolveFields($field['type']);
			}
		}

		$queries = new ObjectType([
			'name' => 'RootQueryType',
			'fields' => $fields
		]);

		return new Schema($queries, null);
	}


	/**
	 * @param $type
	 * @return ObjectType|\Closure
	 */
	private function resolveFields(&$type) {
		if($type instanceof \Closure) {
			return $type($this);
		}

		if(property_exists($type, 'config')){
			foreach($type->config['fields'] as &$field) {
				if(array_key_exists('type', $field)) {
					$field['type'] = $this->resolveFields($field['type']);
				}
			}
		}

		return $type;
	}
}