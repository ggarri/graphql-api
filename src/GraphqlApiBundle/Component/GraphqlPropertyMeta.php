<?php

namespace GraphqlApiBundle\Component;

use GraphqlApiBundle\Service\GraphqlSchema;
use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use GraphQL\Type\Definition\Type;


class GraphqlPropertyMeta
{

	const ANNOTATION_LABEL = 'Graphql';

	/** @var string  */
	public $name;

	/** @var string  */
	public $type;

	/** @var bool */
	public $nullable;

	/** @var string */
	public $refColName;

	/** @var string */
	public $refTargetEntity;

	/** @var bool  */
	public $pk = false;

	/** @var bool  */
	public $unique = false;

	/** @var string @TODO */
	public $desc;

	/** @var array @TODO */
	public $acl;

	/** @var  Annotation[] */
	protected $annotations;


	/**
	 * @param Annotation[] $annotations
	 */
	public function __construct($annotations)
	{
		$this->annotations = $annotations;
		foreach($annotations as $annotation) {
			if($annotation instanceof Column) {
				$this->name = $annotation->name;
				$this->nullable = $annotation->nullable;
				$this->type = $annotation->type;
			}
			if($annotation instanceof Id) {
				$this->pk = true;
			}
			if($annotation instanceof ManyToOne) {
				$this->type = 'ManyToOne';
				$this->refTargetEntity = ucfirst($annotation->targetEntity);
				if($annotation instanceof JoinColumn) {
					$this->nullable = $annotation->nullable;
					$this->pk = $annotation->unique;
					$this->refColName = $annotation->referencedColumnName;
				}
			}
			if($annotation instanceof OneToOne) {
				$this->type = 'OneToOne';
				$this->refTargetEntity = ucfirst($annotation->targetEntity);
				if($annotation instanceof JoinColumn) {
					$this->nullable = $annotation->nullable;
					$this->pk = $annotation->unique;
					$this->refColName = $annotation->referencedColumnName;
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function getGraphqlField() {
		return [
			'type' => $this->getGraphqlType(),
			'description' => $this->desc,
		];
	}

	/**
	 * @return array
	 */
	public function getGraphqlQuery() {
		return [
			'name' => $this->name,
			'description' => $this->desc,
			'type' => $this->getGraphqlType(true)
		];
	}

	/**
	 * @param bool|false $isQuery
	 * @return Type
	 * @throws \Exception
	 */
	protected function getGraphqlType($isQuery = false) {
		switch($this->type) {
			case 'integer':
			case 'smallint':
				$type = Type::int();
				break;
			case 'decimal':
				$type = Type::float();
				break;
			case 'json_array':
				$type = Type::string();
				break;
			case 'string':
			case 'datetime':
			case 'date':
			case 'time':
			case 'text':
			case 'blob':
				$type = Type::string();
				break;
			case 'boolean':
				$type = Type::boolean();
				break;
			case 'ManyToOne':
			case 'OneToOne':
				$type = function(GraphqlSchema $graphSchema) {
					return $graphSchema->getGraphqlEntity($this->refTargetEntity);
				};
				break;
			case 'ManyToMany':
			case 'OneToMany':
				$type = function(GraphqlSchema $graphSchema) {
					return Type::listOf($graphSchema->getGraphqlEntity($this->refTargetEntity));
				};
				break;
			default:
				throw new \Exception("Graphql: Type ".$this->type." not defined");
		}

		if($isQuery === false && $this->nullable === false && !isset($this->refTargetEntity)) {
			$type = Type::nonNull($type);
		}

		return $type;
	}


	/**
	 * TODO: Include element description
	 * @return string
	 */
	public function getDescription() {
		return "@TODO";
	}
}