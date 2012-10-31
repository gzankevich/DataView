<?php

use DataView\Adapter;

class DoctrineORMGetQueryTest extends DoctrineORM
{
	protected function applyFilters($queryBuilder)
	{
		return $queryBuilder;
	}
}

class DoctrineORMTest extends \PhpUnit_Framework_TestCase
{
	public function testJoinRelations()
	{
	}

	public function testGetAliasFromQueryBuilder()
	{

	}

	public function applyFilters()
	{

	}

	public function getQuery()
	{
		$repository = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->getMock();
		$repository
			->expects($this->once())
			->method('createQueryBuilder')
			->will($this->returnValue('test'));

		$entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')->getMock();
		$entityManager
			->expects($this->once())
			->method('getRepository')
			->will($this->returnValue($repository));


		$doctrineORM = new DoctrineORMGetQueryTest($entityManager);
		$doctrineORM->getQuery();
	}
}
