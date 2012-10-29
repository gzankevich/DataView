<?php

namespace DataView;

use DataView\Adapter\AdapterInterface;

class DataView
{
	const SORT_ORDER_ASCENDING = 'ascending';
	const SORT_ORDER_DESCENDING = 'descending';

	private $orderByColumnName, $sortOrder = null;
	private $filters = array();

	/**
	 * Adapter injected here
	 */
	public function __construct(AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Can be a query builder, entity name, array
	 */
	public function setSource($source)
	{
		$this->adapter->setSource($source);
	}

	/**
	 * Add a filter
	 */
	public function addFilter(Filter $filter)
	{
		$this->filters[] = $filter;
	}

	/**
	 * Assign a set of filters
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

	/**
	 * Set the order by column and the sort order
	 */
	public function setOrderBy($columnName, $sortOrder)
	{
		$this->orderByColumnName = $columnName;
		$this->sortOrder = $sortOrder;
	}

	/**
	 * Gets the results from the adapter
	 */
	public function getPager()
	{
		if(!$this->adapter->getSource()) {
			throw new \Exception('Please set a source to fetch the results from');
		}

		$this->adapter->setFilters($this->filters);

		return $this->adapter->getPager(
			$this->adapter->getQuery()
		);
	}
}
