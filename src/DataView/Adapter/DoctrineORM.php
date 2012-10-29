<?php

namespace DataView\Adapter;

/**
 * Doctrine ORM adapter
 */
class DoctrineORM implements IAdapter
{
	protected $source, $tableName = null;
	protected $filters = array();

	/**
	 * {@inheritdoc}
	 */
	public function getColumns($entity)
	{

	}

	/**
	 * Apply the filters to the queryBuilder and return a query which Pagerfanta can work with
	 */
	protected function getQuery()
	{
		// modify $queryBuilder with $filters

		return $queryBuilder->getQuery();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPager()
	{
		$pager = new Pagerfanta(new DoctrineORMAdapter($this->getQuery()));

		return $pager;
	}

	public function setSource($source)
	{
		$this->source = $source;
	}

	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	public function addFilter($filter)
	{
		$this->filters[] = $filter;
	}
}
