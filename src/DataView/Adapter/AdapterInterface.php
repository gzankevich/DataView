<?php

namespace DataView\Adapter;

interface AdapterInterface
{
	/**
	 * Get the columns defined on an entity/document
	 */
	public function getColumns($entity);

	/**
	 * Get the results from the query builder + filters
	 *
	 * The result of this is passed to Pagerfanta by DataView.
	 */
	public function getQuery();

	/**
	 * Set the source 
	 *
	 * This will override the tableName if it is set.
	 */
	public function setSource($source);

	public function setFilters($filters);
}
