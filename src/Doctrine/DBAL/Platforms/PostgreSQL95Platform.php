<?php

namespace Doctrine\DBAL\Platforms;
/**
 * Created by ggarrido at 7/08/16 23:05.
 * Copyright: 2016, Base7booking
 */


class PostgreSQL95Platform extends PostgreSQL92Platform
{
	protected function initializeDoctrineTypeMappings()
	{
		parent::initializeDoctrineTypeMappings();
		$this->doctrineTypeMapping['jsonb'] = 'json_array';
	}
}