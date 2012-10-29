<?php

namespace DataView\Adapter;

/**
 * Doctrine ORM adapter
 */
class DoctrineORM implements AdapterInterface
{
	protected $source, $tableName, $entityManager = null;
	protected $filters = array();

	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getColumns($entity)
	{

	}

	/**
	 * Apply the filters to the queryBuilder and return a query which Pagerfanta can work with
	 */
	public function getQuery()
	{
		$queryBuilder = null;

		if(is_string($this->source)) {
			// source is a table name
			// use any value for the alias, applyFilters() will autodetect it
			$queryBuilder = $this->entityManager->getRepository($this->source)->createQueryBuilder('__anything__');

		} else {
			// source is a QueryBuilder
			$queryBuilder = $this->source;
		}

		return $this->applyFilters($queryBuilder);
	}

	protected function applyFilters($queryBuilder)
	{
		$alias = $this->getAliasFromQueryBuilder($queryBuilder);

		foreach($this->filters as $key => $f) {
			$parameterName = "param_{$key}";

			// build a where clause that looks something like:
			// __anything__.name = :param_0
			$queryBuilder->andWhere("{$alias}.{$f->getColumnName()} {$f->getComparisonType()} :{$parameterName}");
			// bind the parameter - this will protect against SQL injection
			$queryBuilder->setParameter($parameterName, $f->getCompareValue());
		}

		return $queryBuilder;
	}

	protected function getAliasFromQueryBuilder($queryBuilder)
	{
		$dqlSelectParts = $queryBuilder->getDqlPart('select');
		$parts = $dqlSelectParts[0]->getParts();

		return $parts[0];
	}

	/**
	 * Set the source to fetch results from
	 *
	 * This can be a:
	 * - String containing the table/entity name
	 * - QueryBuilder instance
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Assign a set of filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * Gets a pager instance
	 */
	public function getPager($query)
	{
		return new \Pagerfanta\Pagerfanta(new \Pagerfanta\Adapter\DoctrineORMAdapter($query));
	}
}
