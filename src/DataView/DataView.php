<?php

namespace DataView;

use DataView\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * Given a source, this class can apply a set of filters and sort it into a paginated list.
 *
 * @package DataView
 * @author George Zankevich <gzankevich@gmail.com>
 */
class DataView
{
    const SORT_ORDER_ASCENDING = 'ascending';
    const SORT_ORDER_DESCENDING = 'descending';

    private $orderByPropertyPath = null;
    private $sortOrder = null;
    private $filters = array();
    private $columns = array();

    /**
     * Constructor
     *
     * The ORM/ODM adapter is injected here.
     *
     * @param AdapterInterface $adapter The adapter to use (e.g. DoctrineORM)
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Can be a query builder, entity name, array (depends on what the adapter supports)
     *
     * @param mixed $source The source
     *
     * @return null
     */
    public function setSource($source)
    {
        $this->adapter->setSource($source);
    }

    /**
     * Add a filter
     *
     * @param Filter $filter The filter to add
     *
     * @return null
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Assign a set of filters
     *
     * @param array $filters The filters to set
     *
     * @return null
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Add a filter
     *
     * @param Column $column The column to add
     *
     * @return null
     */
    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
    }

    /**
     * Assign a set of filters
     *
     * @param array $columns The columns to set
     *
     * @return null
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Set the order by column and the sort order
     *
     * @param string $propertyPath The property path of the column to order by
     * @param string $sortOrder Ascending or descending
     *
     * @return null
     */
    public function setOrderBy($propertyPath, $sortOrder)
    {
        $this->orderByPropertyPath = $propertyPath;
        $this->sortOrder = $sortOrder;
    }


	/**
	 * Gets the results from the adapter in a pager
	 *
	 * @return Pagerfanta A pager
	 */
	public function getPager()
	{
		if(!$this->adapter->getSource()) {
			throw new SourceNotSetException('Please set a source to fetch the results from');
		}

        $this->adapter->setFilters($this->filters);
        $this->adapter->setOrderBy($this->orderByPropertyPath, $this->sortOrder);

        return $this->adapter->getPager();
    }
}
