<?php

namespace DataView\Adapter;

/**
 * Base class for adapters
 *
 * @package DataView
 * @subpackage Adapter
 * @author George Zankevich <gzankevich@gmail.com>
 */
abstract class BaseAdapter
{
    protected $source, $pager;
    protected $columns = array();
    protected $filters = array();

    /**
     * Can be a query builder, entity name, array (depends on what the adapter supports)
     *
     * @param mixed $source The source
     * @return null
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Returns the source
     *
     * @return mixed The source
     */
    public function getSource()
    {
        return $this->source;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Set the filters to apply
     *
     * @param array $filters The filters
     * @return null
     */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}

    /**
     * Get the pager.
     *
     * @return Pagerfanta
     */
	public function getPager()
	{
        if(!$this->pager) {
            $this->pager = $this->createPager();
        }

        return $this->pager;
	}

    protected abstract function createPager();
}
