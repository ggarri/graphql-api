<?php

namespace GraphqlApiBundle\Command;

/**
 * Created by ggarrido at 7/08/16 13:44.
 */

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\ConvertMappingDoctrineCommand;
use GraphqlApiBundle\Components\GraphqlMetadataExporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class MappingSchemaCommand extends ConvertMappingDoctrineCommand
{
	/**
	 * @var string
	 */
	protected $name = 'graphql-api:graphql:generate-schema';

	/**
	 * @var string
	 */
	private  $namespace;


	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		parent::configure();
		$this->setName($this->name);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->namespace = str_replace('/', '\\', $input->getOption('namespace')). '\\';
		$input->setOption('namespace', $this->namespace);
		parent::execute($input, $output);
	}

	/**
	 * @param string $toType
	 * @param string $destPath
	 * @return GraphqlMetadataExporter
	 */
	protected function getExporter($toType, $destPath)
	{
		return new GraphqlMetadataExporter($this->namespace, $destPath);
	}
}