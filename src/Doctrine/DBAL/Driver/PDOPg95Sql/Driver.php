<?php
/**
 * Created by ggarrido at 7/08/16 23:17.
 * Copyright: 2016, Base7booking
 */


namespace Doctrine\DBAL\Driver\PDOPg95Sql;

use Doctrine\DBAL\Platforms\PostgreSQL95Platform;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as OrigDriver;

class Driver extends OrigDriver
{
	public function createDatabasePlatformForVersion($version)
	{
		return new PostgreSQL95Platform();
	}
}