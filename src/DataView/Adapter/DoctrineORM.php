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
    protected $source, $tableName, $entityManager, $orderByPropertyPath = null;
    protected $columns = array();
    protected $filters = array();
    protected $joinsMade = array();

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
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

        // this is the main entity alias - we need to determine it in case one was specified in the initial querybuilder instance by the user, otherwise one will be created and named __entity__
		$alias = $this->getAliasFromQueryBuilder($queryBuilder);

        // we will need the joins to be in place for when we filter and sort
        foreach($this->getColumns() as $column) {
            $this->joinRelations($column->getPropertyPath(), $queryBuilder);
        }

		$queryBuilder = $this->applyFilters($queryBuilder, $alias);
        $queryBuilder = $this->applyOrderBy($queryBuilder, $alias);

		return $queryBuilder->getQuery();
	}

    protected function applyOrderBy($queryBuilder, $alias)
    {
        foreach($this->columns as $column) {
            if($column->getSortOrder()) {
                $queryBuilder = $this->doApplyOrderBy($queryBuilder, $alias, $column->getPropertyPath(), $column->getSortOrder());
            }
        }

        return $queryBuilder;
    }

    protected function doApplyOrderBy($queryBuilder, $alias, $propertyPath, $sortOrder)
    {
        if(strpos($propertyPath, '.') === false) {
			// we're referencing a relation, do not append the main entity's alias
			$queryBuilder->add('orderBy', "{$alias}.{$propertyPath} {$sortOrder}");

		} else {
            // the orderByPropertyPath will contain a full property path from the main entity
            // we just want the table alias (which will have been joined at this point) and the attribute to sort on
            $parts = explode('.', $propertyPath);
            $attribute = array_pop($parts);
            $tableAlias = array_pop($parts);

            $queryBuilder->add('orderBy', "{$tableAlias}.{$attribute} {$sortOrder}");
		}

		return $queryBuilder;
	}

	/**
	 * Applies the filters to the QueryBuilder instance
	 */
	protected function applyFilters($queryBuilder, $alias)
	{
		foreach($this->filters as $key => $f) {
			$parameterName = "param_{$key}";

			if(strpos($f->getColumnName(), '.') !== false) {
				//$relationPropertyPath = $this->joinRelations($alias.'.'.$f->getColumnName(), $queryBuilder);

                $parts = explode('.', $f->getColumnName());

                $relationPropertyPath = $parts[count($parts) - 2].'.'.$parts[count($parts) - 1];

				$queryBuilder->andWhere("{$relationPropertyPath} {$f->getComparisonType()} :{$parameterName}");
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

	/**
	 * Join any relations in the case that we are processing a property path which references one
	 */
	protected function joinRelations($propertyPath, $queryBuilder, $continue = false)
	{
		$columnNameParts = explode('.', $propertyPath);

        $alias = $this->getAliasFromQueryBuilder($queryBuilder);

		if(count($columnNameParts) > 2) {
            // we are joining multiple relationships, don't use the alias as the first part of the join
            if($continue) {
                $queryBuilder->leftJoin("{$columnNameParts[0]}.{$columnNameParts[1]}", $columnNameParts[1]);

                if(count($columnNameParts == 3)) {
                    // i.e. the propertyPath looks like  table.relation.attribute, meaning that there is no more work to do here
                    return;
                }
            } else {
                // we have not recursed in this method yet meaning that the relation we're joining is directly connected to the main entity, hence prefixing with the alias
                $queryBuilder->join("{$alias}.{$columnNameParts[0]}", $columnNameParts[0]);
            }

			$propertyPath = implode('.', $columnNameParts);

            // this is where we handle multiple levels of relations
            $propertyPath = $this->joinRelations($propertyPath, $queryBuilder, true);
        } elseif(count($columnNameParts) == 2) {
            // e.g. the property path looks like   company.name, where company is a direct relation of the main entity
            // so we need to prefix the main entity alias
            $queryBuilder->leftJoin("{$alias}.{$columnNameParts[0]}", $columnNameParts[0]);
        }    
		
		return $propertyPath;
	}

	/**
	 * Extracts the primary entity alias from a QueryBuilder instance
     *
     * If the user provides a querybuilder instance as a starting point and sets an alias on the main entity then we need to extract it so that we can use it to join all of the relations.
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
}
