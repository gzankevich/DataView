<?php

namespace DataView;

use DataView\Adapter\IAdapter;

class DataView
{
	const SORT_ORDER_ASCENDING = 'ascending';
	const SORT_ORDER_DESCENDING = 'descending';

	private $source, $orderByColumnName, $sortOrder = null;
	private $filters = array();

	/**
	 * Adapter injected here
	 */
	public function __construct(IAdapter $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Can be a query builder, entity name, array
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}

	/**
	 * Add a filter
	 */
	public function addFilter(Filter $filter)
	{
		$this->filters[] = $filter;
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
		$this->adapter->getPager($this->filters);
	}
}
