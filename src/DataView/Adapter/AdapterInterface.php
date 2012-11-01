<?php

namespace DataView\Adapter;

use Pagerfanta\Pagerfanta;

/**
 * Interface for ORM/ODM adapters
 *
 * @package DataView
 * @subpackage Adapter
 * @author George Zankevich <gzankevich@gmail.com>
 */
interface AdapterInterface
{
    /**
     * Can be a query builder, entity name, array (depends on what the adapter supports)
     *
     * @param mixed $source The source
     * @return null
     */
    public function setSource($source);

    /**
     * Returns the source
     *
     * @return mixed The source
     */
    public function getSource();

    /**
     * Set the filters to apply
     *
     * @param array $filters The filters
     * @return null
     */
    public function setFilters($filters);

    /**
     * Set the order by column and the sort order
     *
     * @param string $propertyPath The property path of the column to order by
     * @param string $sortOrder Ascending or descending
     * @return null
     */
    public function setOrderBy($propertyPath, $sortOrder);

    /**
     * Get the pager.
     *
     * @return Pagerfanta
     */
    public function getPager();
}
