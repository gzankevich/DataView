<?php

namespace DataView\Adapter;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use DataView\SourceNotSetException;

/**
 * Doctrine ORM adapter
 */
class DoctrineORM implements AdapterInterface
{
    protected $source, $tableName, $entityManager, $orderByPropertyPath, $sortOrder = null;
    protected $filters, $joinsMade = array();

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
     * Get the results from the query builder + filters
     *
     * The result of this is passed to Pagerfanta by DataView.
     *
     * @return Query
     */
	protected function getQuery()
	{
		if(!$this->source) {
			throw new SourceNotSetException('Please set a source to fetch the results from');
		}

		$queryBuilder = null;

		if(is_string($this->source)) {
			// source is a table name
			// use any value for the alias, applyFilters() will autodetect it
			$queryBuilder = $this->entityManager->getRepository($this->source)->createQueryBuilder('__entity__');

		} elseif($this->source instanceOf \Doctrine\ORM\QueryBuilder) {
			// source is a QueryBuilder
			$queryBuilder = $this->source;
		} else {
			throw new InvalidSourceException('Invalid source of type '.is_object($this->source) ? get_class($this->source) : gettype($this->source).' cannot be processed by the DoctrineORM adapter');
		}

		return $this->applyFilters($queryBuilder);
	}

	/**
	 * Applies the filters to the QueryBuilder instance
	 */
	protected function applyFilters($queryBuilder)
	{
		$alias = $this->getAliasFromQueryBuilder($queryBuilder);

		foreach($this->filters as $key => $f) {
			$parameterName = "param_{$key}";

			if(strpos($f->getColumnName(), '.') !== false) {

				$relationPropertyPath = $this->joinRelations($alias.'.'.$f->getColumnName(), $queryBuilder);
				$queryBuilder->andWhere("{$relationPropertyPath} {$f->getComparisonType()} :{$parameterName}");
				echo '<br/>WHERE: '."{$relationPropertyPath} {$f->getComparisonType()} :{$parameterName} - PARAM=".$f->getCompareValue().'<br/>';

			} else {
				// build a where clause that looks something like:
				// __anything__.name = :param_0
				$queryBuilder->andWhere("{$alias}.{$f->getColumnName()} {$f->getComparisonType()} :{$parameterName}");
			}

			// bind the parameter - this will protect against SQL injection
			$queryBuilder->setParameter($parameterName, $f->getCompareValue());
		}

		return $queryBuilder;
	}

	protected function joinRelations($propertyPath, $queryBuilder)
	{
		// getColumnName is referring to a property path since there is a '.' in it
		// in other words, this is referencing an association

		$columnNameParts = explode('.', $propertyPath);

		var_dump($propertyPath);

		var_dump(count($columnNameParts));
		if(count($columnNameParts) > 2) {
			if(!in_array("{$columnNameParts[0]}.{$columnNameParts[1]}", $this->joinsMade)) {
				// join the association since we're going to be filtering on some column of it
				$queryBuilder->join("{$columnNameParts[0]}.{$columnNameParts[1]}", $columnNameParts[1]);

				$this->joinsMade[] = "{$columnNameParts[0]}.{$columnNameParts[1]}";
				echo '<br/>JOIN: '."{$columnNameParts[0]}.{$columnNameParts[1]}";
			}


			unset($columnNameParts[0]);

			$propertyPath = implode('.', $columnNameParts);



			// recurse!
			$propertyPath = $this->joinRelations($propertyPath, $queryBuilder);
		} else {
		}

		return $propertyPath;
	}

	/**
	 * Extracts the primary entity alias from a QueryBuilder instance
	 */
	protected function getAliasFromQueryBuilder($queryBuilder)
	{
		$dqlSelectParts = $queryBuilder->getDqlPart('select');
		$parts = $dqlSelectParts[0]->getParts();

		return $parts[0];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPager()
	{
		return new Pagerfanta(new DoctrineORMAdapter($this->getQuery()));
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOrderBy($propertyPath, $sortOrder)
	{
		$this->orderByPropertyPath = $propertyPath;
		$this->sortOrder = $sortOrder;
	}
}
