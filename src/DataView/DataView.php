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
    const SORT_ORDER_ASCENDING = 'ASC';
    const SORT_ORDER_DESCENDING = 'DESC';

    private $orderByPropertyPath = null;
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
		if(!$this->adapter->getSource()) {
			throw new SourceNotSetException('Please set a source to fetch the results from');
		}

        $this->adapter->setColumns($this->columns);
        $this->adapter->setFilters($this->filters);

        return $this->adapter->getPager();
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
        // this happens when a relation is not set (i.e. an office has no company assigned to it)
        if(empty($entity)) return;

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


}
