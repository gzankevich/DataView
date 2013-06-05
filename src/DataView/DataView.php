<?php

namespace DataView;

use DataView\Adapter\BaseAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Given a source, this class can apply a set of filters and sort it into a paginated list.
 *
 * @package DataView
 * @author George Zankevich <gzankevich@gmail.com>
 */
class DataView
{
    private $orderByPropertyPath = null;
    private $filters = array();
    private $columns = array();
    private $currentPage = 1;

    /**
     * Constructor
     *
     * The ORM/ODM adapter is injected here.
     *
     * @param BaseAdapter $adapter The adapter to use (e.g. DoctrineORM)
     */
    public function __construct(BaseAdapter $adapter)
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

        return $this;
    }

    public function getSource()
    {
        return $this->source;

        return $this;
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

        return $this;
    }

    /**
     * Remove a filter
     *
     * @param Filter $filter The filter to remove
     *
     * @return null
     */
    public function removeFilter(Filter $filter)
    {
        foreach($this->filters as $key => $f) {
            if($f == $filter) {
                unset($this->filters[$key]);
            }
        }
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

        return $this;
    }

    /**
     * Get the filters to apply
     *
     * @return array An array of Filter instances
     */
    public function getFilters()
    {
        return $this->filters;
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

        return $this;
    }

    /**
     * Set the columns to display
     *
     * @param array $columns The columns to set
     *
     * @return null
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Returns the configured columns
     *
     * @return array An array of Column instances
     */
    public function getColumns()
    {
        return $this->columns;
    }

	/**
	 * Gets the results from the adapter in a pager
	 *
	 * @return Pagerfanta A pager
	 */
	public function getPager()
	{
        $columns = $this->getColumns();

        if(empty($columns)) {
            // it makes no sense to get the pager when there are no columns to display
            throw new NoColumnsAddedException();
        }

        $this->adapter->setColumns($columns);
        $this->adapter->setFilters($this->getFilters());

        $pager = $this->adapter->getPager();

        return $pager;
    }

	/**
     * Get the value for a given column from a record, resolving any relationships along the way
     *
     * If you have entity X which has a one-to-one with entity Y, which has an attribute 'name' then
     * you can get the value of name by passing an instance of entity X and a property path of "Y.name"
	 *
	 * @param mixed $record The record to retrieve the value from
	 * @param string $columnName The name of the column to fetch
	 * @return mixed The value. This can be an array that looks like: array('value' => $value, 'url' => $url) if a link is to be displayed.
	 * @author George Zankevich <george.zankevich.fof@gmail.com> 
	 * @access public
	 */
	public function getEntityValueByPropertyPath($entity, $propertyPath)
	{
        // entity is null when a relation is not set (i.e. an office has no company assigned to it)
        // property path is null for non-mapped columns
        if(!$entity || !$propertyPath) return;

		if(strpos($propertyPath, '.') !== false) {
			// resolve any relationships in the property path by iterating over them
			$parts = explode('.', $propertyPath);
			$getterName = 'get'.ucwords($parts[0]);
			// remove the element of the property path we're processing
			array_shift($parts);

			// join the rest of the propery path into a string and recurse
			$value = $this->getEntityValueByPropertyPath($entity->$getterName(), implode('.', $parts));
		} else {
            // no relationships left to resolve, the value will be in an attribute in $entity
            // use the appropriate getter method to fetch it
			$getterName = 'get'.ucwords($propertyPath);
			$value = $entity->$getterName();
		}

		//if($value === true) $value = 'True';
		//elseif($value === false) $value = 'False';

		return $value;
	}

    /**
     * Apply sorting to a column
     *
     * @param string $propertyPath The property path of the column to apply the sorting to
     * @param string $order The order to sort in (Column::SORT_ORDER_ASCENDING or Column::SORT_ORDER_DESCENDING)
     * @return null
     */
    public function applySortOrder($propertyPath, $order)
    {
        foreach($this->getColumns() as $column) {
            if($column->getPropertyPath() === $propertyPath) {
                $column->setSortOrder($order);
            }
        }
    }
}
